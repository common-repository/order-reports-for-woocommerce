<div id="orfwapp" class="fin-container">
	<div class="fin-head">
		<div class="fin-head-left">
			<span><?php esc_html_e( 'Order Reports', 'orfw' ); ?></span>
			<img src="<?php echo ORFW_BASE_URL; ?>admin/assets/img/arrow-right.svg" class="icon">
			<span><?php esc_html_e( 'Filter & Export', 'orfw' ); ?></span>
		</div>
		<div class="fin-head-right">
			<div class="fin-timeframe">
				<button @click="exportCSV" id="export" class="fin-button flr"><?php _e( 'Export', 'orfw' ); ?></button>
			</div>
		</div>
	</div>

	<div class="fin-content">
		<?php if(isset($summary)) { ?>
			<div class="sales-figures">
				<div class="sales-figure">
					<div class="sf-number">
					<?php echo esc_html($handler->view['info']['qty']); ?>
					</div>
					<div class="sf-title">
						<?php _e('Items sold', 'orfw'); ?>
					</div>
				</div>
				<div class="sales-figure">
					<div class="sf-number">
					{{currencySymbol}}<?php echo esc_html($handler->view['info']['total']); ?>
					</div>
					<div class="sf-title">
						<?php _e('Total', 'orfw'); ?>
					</div>
				</div>
				<div class="sales-figure">
					<div class="sf-number">
					{{currencySymbol}}<?php echo esc_html($handler->view['info']['avg']); ?>
					</div>
					<div class="sf-title">
						<?php _e('Avg. Order Value', 'orfw'); ?>
					</div>
				</div>
				<div class="sales-figure">
					<div class="sf-number">
					<?php echo esc_html($handler->view['info']['avgtime']); ?>
					</div>
					<div class="sf-title">
						<?php _e('Average Time for Sale', 'orfw'); ?>
					</div>
				</div>
				<div class="sales-figure">
					<div class="sf-number">
					{{currencySymbol}}<?php echo esc_html($handler->view['info']['pl']); ?>
					</div>
					<div class="sf-title">
						<?php _e('Profit / Loss', 'orfw'); ?>
					</div>
				</div>
			</div>
		<?php } ?>

		<div class="orders-container">
			<div class="orders-left">
				<form id="form-filter" @submit.prevent="filterOrders">
				
					<div class="orders-menu">
						<div class="om-heading"><?php _e('Order', 'orfw'); ?></div>
						<div class="om-sub">
							<div class="om-sub-left"><?php _e('Status', 'orfw'); ?></div>
							<div class="om-sub-right">
								<select name="status" v-model="filters.status">
									<option value="all"><?php _e('All', 'orfw'); ?></option>
									<option value="completed"><?php _e('Completed', 'orfw'); ?></option>
									<option value="pending"><?php _e('Pending Payment', 'orfw'); ?></option>
									<option value="processing"><?php _e('Processing', 'orfw'); ?></option>
									<option value="on-hold"><?php _e('On hold', 'orfw'); ?></option>
									<option value="cancelled"><?php _e('Cancelled', 'orfw'); ?></option>
									<option value="refunded"><?php _e('Refunded', 'orfw'); ?></option>
									<option value="failed"><?php _e('Failed', 'orfw'); ?></option>
								</select>
							</div>
						</div>
						<div class="om-sub">
							<div class="om-sub-left"><?php _e('Total', 'orfw'); ?></div>
							<div class="om-sub-right">
								<select name="totalthan" v-model="filters.totalthan">
									<option value="greater"><?php _e('Greater than', 'orfw'); ?></option>
									<option value="lower"><?php _e('Lower than', 'orfw'); ?></option>
								</select>
							</div>
						</div>
						<div class="om-sub">
							<div class="om-sub-left"></div>
							<div class="om-sub-right"><input type="number" name="total" v-model="filters.total" data-validate="money"></div>
						</div>
						<div class="om-heading"><?php _e('Date', 'orfw'); ?></div>
						<div class="om-sub">
							<div class="om-sub-left"><?php _e('Type', 'orfw'); ?></div>
							<div class="om-sub-right">
								<select name="datetype" v-model="filters.datetype">
									<option value="date_created"><?php _e('Date created', 'orfw'); ?></option>
									<option value="date_paid"><?php _e('Date paid', 'orfw'); ?></option>
									<option value="date_invoice"><?php _e('Invoice date', 'orfw'); ?> (WC Invoices & Packing Slips)</option>
								</select>
							</div>
						</div>
						<div class="om-sub">
							<div class="om-sub-left"><?php _e('Start', 'orfw'); ?></div>
							<div class="om-sub-right"><input type="text" id="datestart" name="datestart" data-validate="date" class="datepicker" v-model="filters.datestart"></div>
						</div>
						<div class="om-sub">
							<div class="om-sub-left"><?php _e('End', 'orfw'); ?></div>
							<div class="om-sub-right"><input type="text" id="dateend" name="dateend" data-validate="date" class="datepicker" v-model="filters.dateend"></div>
						</div>
						<div class="om-heading"><?php _e('Payment', 'orfw'); ?></div>
						<div class="om-sub">
							<div class="om-sub-left"><?php _e('Method', 'orfw'); ?></div>
							<div class="om-sub-right">
								<select name="gateway" v-model="filters.gateway">
									<option></option>
									<?php foreach($handler->view['gwlist'] as $gwid=>$gwname) { ?>
										<option value="<?php echo $gwid; ?>"><?php echo $gwname; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="om-sub">
							<div class="om-sub-left"></div>
							<div class="om-sub-right"><input type="submit" class="fin-button flr" value="<?php esc_attr_e( 'Filter', 'orfw' ); ?>"></div>
						</div>

						<div class="accpro">
							<div>
								<img src="<?php echo ORFW_BASE_URL; ?>admin/assets/img/icon.jpg">
								<div>
									<h2>Finpose</h2>
									<h4><?php _e( 'Accounting Plugin for WooCommerce', 'orfw' ); ?></h4>
								</div>
							</div>
							<div>
								<div>
									<h4><?php _e( 'Modules', 'orfw' ); ?></h4>
									<ul>
										<li><?php _e( 'Inventory', 'orfw' ); ?></li>
										<li><?php _e( 'Spendings', 'orfw' ); ?></li>
										<li><?php _e( 'Tax', 'orfw' ); ?></li>
										<li><?php _e( 'Accounts', 'orfw' ); ?></li>
										<li><?php _e( 'Orders', 'orfw' ); ?></li>
										<li><?php _e( 'Reports', 'orfw' ); ?></li>
									</ul>
								</div>
								<div>
									<a class="fin-button flr" href="https://fitpresso.com/?autologin_code=VlH3sg8JVufnmwSNUIl2OicBrEYx0HjY" target="_blank"><?php _e( 'Live Demo', 'orfw' ); ?></a>
									<a class="fin-button flr" href="https://finpose.com/?utm_source=orfw&utm_medium=plugins" target="_blank"><?php _e( 'More Information', 'orfw' ); ?></a>
								</div>
							</div>
						</div>
					</div>
				</form>

				
			</div>
			<div class="orders-right">
				<div class="orders-content">
					<table class="fin-table fin-table-thin fin-table-export" cellpadding="0" cellspacing="0">
						<thead>
							<tr>
								<th><?php _e( 'ID', 'orfw' ); ?></th>
								<th><?php _e('Date created', 'orfw'); ?></th>
								<th><?php _e('Status', 'orfw'); ?></th>
								<th><?php _e('Payment Method', 'orfw'); ?></th>
								<th><?php _e('Customer', 'orfw'); ?></th>
								<th><?php _e('Country', 'orfw'); ?></th>
								<th class="tar"><?php _e('Tax', 'orfw'); ?> ({{currencySymbol}})</th>
								<th class="tar"><?php _e('Shipping', 'orfw'); ?> ({{currencySymbol}})</th>
								<th class="tar"><?php _e('Shipping Tax', 'orfw'); ?> ({{currencySymbol}})</th>
								<th class="tar"><?php _e('Subtotal', 'orfw'); ?> ({{currencySymbol}})</th>
								<th class="tar"><?php _e('Total', 'orfw'); ?> ({{currencySymbol}})</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(order, index) in orders">
								<td><a :href="order.url" target="_blank">#{{order.id}}</a></td>
								<td>{{order.date}}</td>
								<td>{{printStatus(order.status)}}</td>
								<td>{{order.pm}}</td>
								<td>{{order.cus}}</td>
								<td>{{order.geo}}</td>
								<td class="tar">{{floatFix(order.tax)}}</td>
								<td class="tar">{{floatFix(order.shipamount)}}</td>
								<td class="tar">{{floatFix(order.shiptax)}}</td>
								<td class="tar">{{floatFix(order.st)}}</td>
								<td class="tar">{{floatFix(order.total)}}</td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<th class="tal b"><?php _e('Totals', 'orfw'); ?></th>
								<th colspan="5">
								<th>{{floatFix(totals.tax)}}</th>
								<th>{{floatFix(totals.shipamount)}}</th>
								<th>{{floatFix(totals.shiptax)}}</th>
								<th>{{floatFix(totals.st)}}</th>
								<th class="b">{{floatFix(totals.total)}}</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>


	</div>

</div>
