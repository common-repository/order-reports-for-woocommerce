<?php
/**
 * Class for Sales
 *
 *
 * @link              https://finpose.com
 * @since             1.1.0
 * @package           Finpose
 * @author            info@finpose.com
 */
if ( !class_exists( 'orfw_orders' ) ) {
  class orfw_orders extends orfw_app {

    public $v = 'buildSalesReport';
    public $p = '';

    public $selyear;
		public $selmonth;
		public $selq;

    public $success = false;
    public $message = '';
    public $results = array();
    public $callback = '';

    /**
	 * Reporting Constructor
	 */
    public function __construct($v = 'pageOrders') {
      parent::__construct();
      $this->selyear = $this->curyear;
			$this->selmonth = $this->curmonth;
			$this->selq = $this->curq;

      // POST verification, before processing
      if($this->post) {
        $validated = $this->validate();
        if($validated) {
          $verified = wp_verify_nonce( $this->post['nonce'], 'orfwpost' );
          $can = current_user_can( 'view_woocommerce_reports' );
          if($verified && $can) {
            if(isset($this->post['process'])) {
              $p = $this->post['process'];
              unset(
                $this->post['process'],
                $this->post['handler'],
                $this->post['action'],
                $this->post['nonce'],
                $this->post['_wp_http_referer']
              );
              $this->$p();
            }
          }
        }
      }

      if($v != 'ajax') { $this->$v(); }

      if($this->ask->errmsg) { $this->view['errmsg'] = $this->ask->errmsg; }
		}

		/**
		 * Validate all inputs before use
		 */
		public function validate($vars = array()) {
			$status = true;

			if(!$vars) { $vars = $this->post; }
			foreach ($vars as $pk => $pv) {
				if($pk == 'year') {
					if(intval($pv)>2030||intval($pv)<2010) {
						$status = false;
						$this->message = esc_html__( 'Year provided is invalid', 'orfw' );
					}
				}
				if($pk == 'month') {
					if(intval($pv)>12||intval($pv)<1) {
						$status = false;
						$this->message = esc_html__( 'Month provided is invalid', 'orfw' );
					}
				}
				if(in_array($pk, array('datestart', 'dateend'))) {
					if($pv) {
						if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $pv)) {
							$status = false;
							$this->message = esc_html__( 'Date format provided is invalid', 'finpose' );
						}
					}
				}
				if($pk == 'totalthan') {
					if(!in_array($pv, array('lower', 'greater'))) {
            $status = false;
            $this->message = esc_html__( 'Total selector can only be lower or greater', 'finpose' );
          }
				}
				if($pk == 'datetype') {
					if(!in_array($pv, array('date_created', 'date_paid', 'date_invoice'))) {
            $status = false;
            $this->message = esc_html__( 'Invalid date type selector', 'finpose' );
          }
				}
				if($pk == 'status') {
					if(!in_array($pv, array('all', 'completed', 'pending', 'processing', 'on-hold', 'cancelled', 'refunded', 'failed'))) {
            $status = false;
            $this->message = esc_html__( 'Invalid date type selector', 'finpose' );
          }
				}
			}

		return $status;
		}

		public function pageOrders() {
			$bigws = WC()->payment_gateways->get_available_payment_gateways();
			$this->view['gwlist'] = array();
			if( $bigws ) {
				foreach( $bigws as $slug=>$bigw ) {
					$this->view['gwlist'][$slug] = $bigw->title;
				}
			}
		}

		public function getOrders() {
			if(!$this->post['filters']) return false;
			$this->payload['filters'] = $filters = json_decode(stripslashes($this->post['filters']), true);
			if(!$this->validate($this->payload['filters'])) { return false; }

      $mstart = $this->selyear.'-'.$this->selmonth.'-01';
			$mend = $this->selyear.'-'.$this->addZero($this->selmonth+1).'-01';
			if($this->selmonth=='12') { $mend = ($this->selyear+1).'-01-01'; }
			if($filters['datestart']) { $mstart = $filters['datestart']; }
			if($filters['dateend']) { $mend = $filters['dateend']; }

			$this->payload['filters']['datestart'] = $mstart;
			$this->payload['filters']['dateend'] = $mend;
			
			$datetype = "date_created";
			if($filters['datetype']) { $datetype = $filters['datetype']; }

			$status = "all";
			if($filters['status']) { $status = $filters['status']; }

			$gateway = "";
			if($filters['gateway']) { $gateway = $filters['gateway']; }
			$this->payload['total'] = "";
			if($filters['total']) { $this->payload['total'] = $filters['total']; }
			$this->payload['totalthan'] = "greater";
			if($filters['totalthan']) { $this->payload['totalthan'] = $filters['totalthan']; }

      $msu = strtotime($mstart);
			$mse = strtotime($mend)-1;

			if($msu>$mse) {
				$this->message = "End date should be after start date";
				return false;
			}

			$args =  array('limit' => -1);

			if($status != 'all') { $args['status'] = $status; }
			if($gateway) { $args['payment_method'] = $gateway; }
			if($datetype=='date_invoice') {
				$args['meta_key'] = '_wcpdf_invoice_date';
				$args['meta_compare'] = 'BETWEEN';
				$args['meta_value'] = array( $msu, $mse );
				$args['meta_type'] = 'numeric';
			} else {
				$args[$datetype] = $mstart."...".$mend;
			}
			$orders = wc_get_orders( $args );

			$info = array('qty'=>0,'total'=>0,'avg'=>0,'orders'=>array(), 'pm'=> array(), 'bs'=>array(), 'geo'=>array());
			$totals = array('tax'=>0,'shiptax'=>0,'shipamount'=>0,'st'=>0,'total'=>0);
			$numorders = 0;
			$viewOrders = array();
			foreach ($orders as $order) {
				$negative = false;
				if ( is_a( $order, 'WC_Order_Refund' ) ) {
					$order = wc_get_order( $order->get_parent_id() );
					$negative = true;
				}

				$include = true;
				$order_data = $order->get_data();
				$total = $order_data['total'];
				
				if($filters['total']) {
					if($filters['totalthan']=="greater") { if($total<$filters['total']) { $include=false; }}
					if($filters['totalthan']=="lower") { if($total>$filters['total']) { $include=false; }}
				}

				if($include) {
					//$info['qty'] += $order->get_item_count();
					//$subtotal = $order->get_subtotal();
					//$info['total'] += $subtotal;
					$geo = $order_data['billing']['country'];
					$gn = WC()->countries->countries[ $geo ];
					$pm = $order_data['payment_method_title'];
					$cus = new WC_Customer($order_data['customer_id']);

					//$order_meta = get_post_meta($order->get_meta_data());
					//print_r($order->get_meta_data());

					$od['id'] = $order_data['id'];
					$od['status'] = $order_data['status'];
					$od['url'] = $order->get_edit_order_url();
					$oddate = $order->get_date_created();
					$odtime = strtotime($oddate);
					$od['date'] = date('Y-m-d H:i', $odtime);
					$od['pm'] = $pm;
					$od['cus'] = $cus->get_first_name().' '.$cus->get_last_name();
					$od['geo'] = $gn;
					$od['tax'] = $negative?-$order_data['total_tax']:$order_data['total_tax'];
					$od['shiptotal'] = $negative?-$order_data['shipping_total']:$order_data['shipping_total'];
					$od['shiptax'] = $negative?-$order_data['shipping_tax']:$order_data['shipping_tax'];
					$od['shipamount'] = $negative?-($od['shiptotal']-$od['shiptax']):($od['shiptotal']-$od['shiptax']);
					$od['st'] = $negative?-($order_data['total'] - $order_data['total_tax'] - $order_data['shipping_total']):($order_data['total'] - $order_data['total_tax'] - $order_data['shipping_total']);
					$od['total'] = $negative?-$order_data['total']:$order_data['total'];
					$viewOrders[] = $od;

					$totals['tax'] += $od['tax'];
					$totals['shiptax'] += $od['shiptax'];
					$totals['shipamount'] += $od['shipamount'];
					$totals['st'] += $od['st'];
					$totals['total'] += $od['total'];

					//$odate = intval(date('d', strtotime($order->get_date_completed())));
					//$sales[$odate-1] += $subtotal;
					$numorders++;
				}
			}
			$this->payload['info'] = array();
			$this->payload['orders'] = $viewOrders;
			$this->payload['totals'] = $totals;
			$this->success = true;
		}

  }
}
