<script>
	var asInitVals = new Array();
	
	$(document).ready(function() {
	    var oTable = $('.dataTable').dataTable(
			{
				"bProcessing": true,
		        "bServerSide": true,
		        "sAjaxSource": "<?php print site_url($ajaxurl);?>",
				"oLanguage": { "sSearch": "Search "},
			<?php if($this->config->item('infinite_scroll')):?>
				"bScrollInfinite": true,
			    "bScrollCollapse": true,
			    "sScrollY": "500px",
			<?php endif; ?>
			<?php if(isset($sortdisable)):?>
				"aoColumnDefs": [ 
				            { "bSortable": false, "aTargets": [ <?php print $sortdisable; ?> ] }
				 ],
			<?php endif;?>
			    "fnServerData": function ( sSource, aoData, fnCallback ) {
		            $.ajax( {
		                "dataType": 'json', 
		                "type": "POST", 
		                "url": sSource, 
		                "data": aoData, 
		                "success": fnCallback
		            } );
		        }
			}
		);

		$('tfoot input').keyup( function () {
			/* Filter on the column (the index) of this element */
			oTable.fnFilter( this.value, $('tfoot input').index(this) );
		} );

		/*
		 * Support functions to provide a little bit of 'user friendlyness' to the textboxes in 
		 * the footer
		 */
		$('tfoot input').each( function (i) {
			asInitVals[i] = this.value;
		} );

		$('tfoot input').focus( function () {
			if ( this.className == 'search_init' )
			{
				this.className = '';
				this.value = '';
			}
		} );

		$('tfoot input').blur( function (i) {
			if ( this.value == '' )
			{
				this.className = 'search_init';
				this.value = asInitVals[$('tfoot input').index(this)];
			}
		} );

		$( '#assign_deliveryzone' ).autocomplete({
			source: '<?php print site_url('ajax/getzone')?>',
			method: 'post',
			minLength: 2
		});

		$( '#assign_deliverycity' ).autocomplete({
			source: '<?php print site_url('ajax/getcities')?>',
			method: 'post',
			minLength: 2
		});

		
		$('#search_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });
		
		$('#search_deliverytime').change(function(){
			oTable.fnFilter( this.value, $('tfoot input').index(this) );
		});

		/*Delivery process mandatory*/
		$('#search_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });
		$('#assign_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });
		
		$('#doAssign').click(function(){
			var assigns = '';
			var date_assign = $('.assign_date:checked').val();
			var city_assign = $('.assign_city:checked').val();

			
			if(date_assign == '' || city_assign == '' ){

				alert('Please select one or more delivery orders');

			}else{

				$('#disp_deliverycity').html(city_assign);
				$('#disp_deliverytime').html(date_assign);

				//$('.assign_check:checked').each(function(){

				var city_assign_class = city_assign.replace(' ','_');
				$('.' + date_assign +'_'+ city_assign_class).each(function(){

					var zone = date_assign + ' | ' +$('#'+this.value).html() +' | '+ city_assign;

					zone += '<input type="checkbox" name="assign_check_dev[]" value="'+this.value+'" class="id_assign">';

					assigns += '<li style="padding:5px;border-bottom:thin solid grey;margin-left:0px;"><strong>'+this.value + '</strong> <br /> '+ zone +'</li>';
				});

				$.post('<?php print site_url('admin/delivery/ajaxdevicecap');?>',{ assignment_date: date_assign,assignment_zone: $('#assign_deliveryzone').val(),assignment_city: city_assign }, function(data) {
					$('#dev_list').html(data.html);
				},'json');

				$('#trans_list').html(assigns);
				$('#assign_dialog').dialog('open');

			}
		});
		
		$('#getDevices').click(function(){
			if($('#assign_deliverytime').val() == ''){
				alert('Please specify intended delivery time');
			}else{
				//alert($('#assign_deliverytime').val());
				$.post('<?php print site_url('admin/delivery/ajaxdevicecap');?>',{ assignment_date: $('#assign_deliverytime').val(),assignment_zone: $('#assign_deliveryzone').val(),assignment_city: $('#assign_deliverycity').val() }, function(data) {
					$('#dev_list').html(data.html);
				},'json');
			}
		});
		
		$('#assign_dialog').dialog({
			autoOpen: false,
			height: 300,
			width: 800,
			modal: true,
			buttons: {
				"Assign to Device": function() {
					var device_id = $("input[name='dev_id']:checked").val();

					if($('#assign_deliverytime').val() == '' || device_id == '' || device_id == undefined){
						alert('Please specify date and or device.');
					}else{
						var delivery_ids = [];
						i = 0;
						$('.id_assign:checked').each(function(){
							delivery_ids[i] = $(this).val();
							i++;
						}); 
						$.post('<?php print site_url('admin/delivery/ajaxassignzone');?>',
							{ 
								assignment_device_id: device_id,
								'delivery_id[]':delivery_ids,
								assignment_timeslot: $('.timeslot:checked').val(),
								assignment_zone: $('#assign_deliveryzone').val(), 
								assignment_city: $('#disp_deliverycity').html() }, 
								function(data) {
								if(data.result == 'ok'){
									//redraw table
									oTable.fnDraw();
									$('#assign_dialog').dialog( "close" );
								}
						},'json');
					}
					
				},
				Cancel: function() {
					$('#dev_list').html("");
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				//allFields.val( "" ).removeClass( "ui-state-error" );
				$('#assign_deliverytime').val('');
			}
		});
		/*
		function refresh(){
			oTable.fnDraw();
			setTimeout(refresh, 10000);
		}

		refresh();
		*/
	} );
	
	
</script>
<?php if(isset($add_button)):?>
	<div class="button_nav">
		<?php echo anchor($add_button['link'],$add_button['label'],'class="button add"')?>
	</div>
<?php endif;?>
<?php echo $this->table->generate(); ?>

<div id="assign_dialog" title="Assign Selection to Device">
	<table style="width:100%;border:0;margin:0;">
		<tr>
			<td style="width:50%;border:0;margin:0;vertical-align: top">
				<h4>Delivery Orders :</h4>
				<ul id="trans_list" style="border-top:thin solid grey;list-style-type:none;padding-left:0px;"></ul>
			</td>
			<td style="width:50%;border:0;margin:0;vertical-align: top">
				<table style="margin: 0px;border: 0px;">
					<tr>
						<td>
							City : <span id="disp_deliverycity" style="font-weight: bold"></span>
						</td>
						<td>
							Delivery Time : <span id="disp_deliverytime" style="font-weight: bold" ></span>
						</td>
					</tr>
				</table>
				<ul id="dev_list" style="border-top:thin solid grey;list-style-type:none;padding-left:0px;"></ul>
			</td>
		</tr>
	</table>
</div>