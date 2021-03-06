<script>
	var asInitVals = new Array();

	$(document).ready(function() {
	    var oTable = $('.dataTable').dataTable(
			{
				"bProcessing": true,
		        "bServerSide": true,
		        "sAjaxSource": "<?php print site_url($ajaxurl);?>",
				"oLanguage": { "sSearch": "Search "},
                "sPaginationType": "full_numbers",
                "sDom": 'T<"clear">lfrtip',
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


		$('#search_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });

		$('#search_deliverytime').change(function(){
			oTable.fnFilter( this.value, $('tfoot input').index(this) );
		});

		/*Delivery process mandatory*/
		$('#search_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });
		$('#assign_deliverytime').datepicker({ dateFormat: 'yy-mm-dd' });

        $('#assign_all').click(function(){
            if($('#assign_all').is(':checked')){
                $('.assign_check').attr('checked', true);
            }else{
                $('.assign_check').attr('checked', false);
            }
        });

		$('#doAssign').click(function(){
			var assigns = '';
			var count = 0;
			$('.assign_check:checked').each(function(){
				assigns += '<li style="padding:5px;border-bottom:thin solid grey;margin-left:0px;">'+this.value+'</li>';
				count++;
			});

			if(count > 0){
				$('#trans_list').html(assigns);
				$('#assign_dialog').dialog('open');
			}else{
				alert('Please select one or more delivery orders');
			}
		});

        $('#download-csv').on('click',function(){
            var flt = $('tfoot td input, tfoot td select');
            var dlfilter = [];

            flt.each(function(){
                var name = this.name;
                var val = this.value;
                dlfilter.push({ name : name, value : val });
            });
            console.log(dlfilter);

            var sort = oTable.fnSettings().aaSorting;
            console.log(sort);

            $.post('<?php print base_url() ?>admin/dl/dispatch',
                {
                    datafilter : dlfilter,
                    sort : sort[0],
                    sortdir : sort[1]
                },
                function(data) {
                    if(data.status == 'OK'){
                        console.log(data.data.urlcsv);
                        window.location.href = data.data.urlcsv;

                    }
                },'json');

            //return false;
            event.preventDefault();
        });


        $('#doLabel').click(function(){
            var assigns = [];
            var count = 0;
            $('.assign_check:checked').each(function(){
                assigns.push(this.value);
                count++;
            });

            if(count > 0){
                $.post(
                    '<?php print base_url().'ajax/printsession'; ?>',
                    { ids: assigns },
                    function(data){
                        if(data.result == 'OK'){
                            var delivery_id = 'SESS:' + data.session;
                            var res = $('#label_resolution').val();
                            var col = $('#label_columns').val();
                            var cell_height = $('#label_cell_height').val();
                            var cell_width = $('#label_cell_width').val();
                            var mright = $('#label_margin_right').val();
                            var mbottom = $('#label_margin_bottom').val();

                            $('#label_id').val(delivery_id);
                            var src = '<?php print base_url() ?>admin/prints/label/' + delivery_id  + '/' +  res +'/' +  cell_height + '/' + cell_width + '/' + col +'/'+ mright +'/'+ mbottom;
                            $('#label_frame').attr('src',src);
                            $('#label_dialog').dialog('open');
                        }
                    },'json');

                //$('#label_dialog').dialog('open');
            }else{
                alert('Please select one or more delivery orders');
            }
        });

        $('#label_refresh').on('click',function(){
            var delivery_id = $('#label_id').val();
            var res = $('#label_resolution').val();
            var col = $('#label_columns').val();
            var cell_height = $('#label_cell_height').val();
            var cell_width = $('#label_cell_width').val();
            var mright = $('#label_margin_right').val();
            var mbottom = $('#label_margin_bottom').val();
            var fsize = $('#label_font_size').val();
            var codetype = $('#label_code_type').val();

            var src = '<?php print base_url() ?>admin/prints/label/' + delivery_id + '/' + res + '/' +  cell_height + '/' + cell_width + '/' + col +'/'+ mright +'/'+ mbottom +'/'+ fsize +'/'+ codetype;

            $('#label_frame').attr('src',src);
        });

        $('#label_default').on('click',function(){
            var delivery_id = $('#label_id').val();
            var res = $('#label_resolution').val();
            var col = $('#label_columns').val();
            var cell_height = $('#label_cell_height').val();
            var cell_width = $('#label_cell_width').val();
            var mright = $('#label_margin_right').val();
            var mbottom = $('#label_margin_bottom').val();
            var fsize = $('#label_font_size').val();
            var codetype = $('#label_code_type').val();

            $.post(
                '<?php print base_url();?>ajax/printdefault',
                {
                    delivery_id : delivery_id,
                    res : res,
                    col : col,
                    cell_height : cell_height,
                    cell_width : cell_width,
                    mright : mright,
                    mbottom : mbottom,
                    fsize : fsize,
                    codetype : codetype
                },
                function(data){
                    if(data.result == 'OK'){
                        alert('Setting saved as default');
                    }else{
                        alert('Setting can not be saved, sorry.');
                    }
                },'json'
                );

            $('#label_frame').attr('src',src);
        });


		$('table.dataTable').click(function(e){
			if ($(e.target).is('.changestatus')) {
				var delivery_id = e.target.id;
				$('#change_id').html(delivery_id);
				$('#changestatus_dialog').dialog('open');
			}

			if ($(e.target).is('.printslip')) {
				var delivery_id = e.target.id;
				$('#print_id').val(delivery_id);
				var src = '<?php print base_url() ?>admin/prints/deliveryslip/' + delivery_id;

				$('#print_frame').attr('src',src);
				$('#print_dialog').dialog('open');
			}

            if ($(e.target).is('.printlabel')) {
                var delivery_id = e.target.id;
                var res = $('#label_resolution').val();
                var col = $('#label_columns').val();
                var cell_height = $('#label_cell_height').val();
                var cell_width = $('#label_cell_width').val();
                var mright = $('#label_margin_right').val();
                var mbottom = $('#label_margin_bottom').val();
                var fsize = $('#label_font_size').val();
                var codetype = $('#label_code_type').val();
                $('#label_id').val(delivery_id);
                var src = '<?php print base_url() ?>admin/prints/label/' + delivery_id + '/' + res + '/' +  cell_height + '/' + cell_width + '/' + col +'/'+ mright +'/'+ mbottom +'/'+ fsize +'/'+ codetype;

                $('#label_frame').attr('src',src);
                $('#label_dialog').dialog('open');
            }

			if ($(e.target).is('.view_detail')) {
				var delivery_id = e.target.id;
				var src = '<?php print base_url() ?>admin/prints/deliveryview/' + delivery_id;

				$('#view_frame').attr('src',src);
				$('#view_dialog').dialog('open');
			}

		});

		$('#getDevices').click(function(){
			if($('#assign_deliverytime').val() == ''){
				alert('Please specify intended delivery time');
			}else{
				//alert($('#assign_deliverytime').val());
				$.post('<?php print site_url('admin/delivery/ajaxdevicecap');?>',{ assignment_date: $('#assign_deliverytime').val(),assignment_zone: $('#assign_deliveryzone').val() }, function(data) {
					$('#dev_list').html(data.html);
				},'json');
			}
		});


		$('#assign_dialog').dialog({
			autoOpen: false,
			height: 300,
			width: 600,
			modal: true,
			buttons: {
				"Assign to Device": function() {
					if($('#assign_deliverytime').val() == ''){
						alert('Please specify date.');
					}else{
						var device_id = $("input[name='dev_id']:checked").val();
						var delivery_ids = [];
						i = 0;
						$('.assign_check:checked').each(function(){
							delivery_ids[i] = $(this).val();
							i++;
						});
						$.post('<?php print site_url('admin/delivery/ajaxassignzone');?>',{ assignment_device_id: device_id,'delivery_id[]':delivery_ids, assignment_zone: $('#assign_deliveryzone').val() }, function(data) {
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

		$('#changestatus_dialog').dialog({
			autoOpen: false,
			height: 250,
			width: 400,
			modal: true,
			buttons: {
				"Confirm Delivery Orders": function() {
					var delivery_id = $('#change_id').html();

					$.post('<?php print site_url('admin/delivery/ajaxchangestatus');?>',{
						'delivery_id':delivery_id,
						'new_status': $('#new_status').val(),
						'actor': $('#actor').val()
					}, function(data) {
						if(data.result == 'ok'){
							//redraw table
							oTable.fnDraw();
							$('#changestatus_dialog').dialog( "close" );
						}
					},'json');
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				//allFields.val( "" ).removeClass( "ui-state-error" );
				$('#confirm_list').html('');
			}
		});

		$('#print_dialog').dialog({
			autoOpen: false,
			height: 400,
			width: 1050,
			modal: true,
			buttons: {
				Print: function(){
					var pframe = document.getElementById('print_frame');
					var pframeWindow = pframe.contentWindow;
					pframeWindow.print();
				},
				"Download PDF": function(){
					var print_id = $('#print_id').val();
					var src = '<?php print base_url() ?>admin/prints/deliveryslip/' + print_id + '/pdf';
					window.location = src;
					//alert(src);
				},
				Close: function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {

			}
		});

		$('#view_dialog').dialog({
			autoOpen: false,
			height: 600,
			width: 900,
			modal: true,
			buttons: {
				Print: function(){
					var pframe = document.getElementById('view_frame');
					var pframeWindow = pframe.contentWindow;
					pframeWindow.print();
				},
				Close: function() {
					oTable.fnDraw();
					$( this ).dialog( "close" );
				}
			},
			close: function() {

			}
		});

        $('#label_dialog').dialog({
            autoOpen: false,
            height: 600,
            width: 1050,
            modal: true,
            buttons: {

                Print: function(){
                    var pframe = document.getElementById('label_frame');
                    var pframeWindow = pframe.contentWindow;
                    pframeWindow.print();
                },
                /*
                "Download PDF": function(){
                    var print_id = $('#label_id').val();
                    var col = $('#label_columns').val();
                    var res = $('#label_resolution').val();
                    var cell_height = $('#label_cell_height').val();
                    var cell_width = $('#label_cell_width').val();
                    var mright = $('#label_margin_right').val();
                    var mbottom = $('#label_margin_bottom').val();
                    var src = '<?php print base_url() ?>admin/prints/label/' + print_id + '/' + res + '/' +  cell_height + '/' + cell_width + '/' + col +'/'+ mright +'/'+ mbottom + '/pdf';
                    window.location = src;
                },
                */
                Close: function() {
                    $( this ).dialog( "close" );
                }
            },
            close: function() {

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
<?php print form_checkbox('assign_all',1,FALSE,'id="assign_all"');?> Select All
<?php if(isset($add_button)):?>
	<div class="button_nav">
		<?php echo anchor($add_button['link'],$add_button['label'],'class="button add"')?>
	</div>
<?php endif;?>
<div class="button_nav">
    <span id="download-csv" class="button" style="cursor:pointer">
        Download Excel
    </span>
</div>
<br />
<?php echo $this->table->generate(); ?>

<div style="text-align:right;margin-top:12px;">
<?php

    print form_button('do_label','Print Selection Label','id="doLabel"');

?>
</div>

<div id="assign_dialog" title="Assign Selection to Device">
	<table style="width:100%;border:0;margin:0;">
		<tr>
			<td style="width:50%;border:0;margin:0;">
				Delivery Orders :
			</td>
			<td style="width:50%;border:0;margin:0;">
				Select Zone :<br />
				<input id="assign_deliveryzone" type="text" value=""><br />
				Delivery Time :<br />
				<input id="assign_deliverytime" type="text" value="">
				<?php print form_button('getdevices','Get Devices','id="getDevices"');?>
			</td>
		</tr>
		<tr>
			<td style="overflow:auto;">
				<ul id="trans_list" style="border-top:thin solid grey;list-style-type:none;padding-left:0px;"></ul>
			</td>
			<td>
				<ul id="dev_list" style="border-top:thin solid grey;list-style-type:none;padding-left:0px;"></ul>
			</td>
		</tr>
	</table>
</div>

<div id="changestatus_dialog" title="Change Delivery Orders">
	<table style="width:100%;border:0;margin:0;">
		<tr>
			<td style="width:250px;vertical-align:top">
				<strong>Delivery ID : </strong><span id="change_id"></span><br /><br />
				<?php
					$status_list = $this->config->item('status_colors');
					$status_list = array_keys($status_list);

					$sl = array();
					foreach($status_list as $s){
						$sl[$s]=$s;
					}

					$actor = $this->config->item('actors_title');


					print 'Actor <br />';
					print form_dropdown('actor',$actor,'','id="actor"').'<br /><br />';
					print ' New Status<br />';
					print form_dropdown('new_status',$sl,'','id="new_status"');

				?>
			</td>
		</tr>
	</table>
</div>

<div id="print_dialog" title="Print" style="overflow:hidden;padding:8px;">
	<input type="hidden" value="" id="print_id" />
	<iframe id="print_frame" name="print_frame" width="100%" height="100%"
    marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto"
    title="Dialog Title">Your browser does not suppr</iframe>
</div>

<div id="view_dialog" title="Order Detail" style="overflow:hidden;padding:8px;">
	<input type="hidden" value="" id="print_id" />
	<iframe id="view_frame" name="print_frame" width="100%" height="100%"
    marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto"
    title="Dialog Title">Your browser does not suppr</iframe>
</div>

<div id="label_dialog" title="Print Label" style="overflow:hidden;padding:8px;">
    <div style="border-bottom:thin solid #ccc;">
        Print options :
        <label>Res
                <input type="text" class="label-opt" value="<?php print $resolution ?>" id="label_resolution" /> ppi
        </label>
        <label>Width
                <input type="text" class="label-opt" value="<?php print $cell_width ?>" id="label_cell_width" /> px
        </label>
        <label>Height
                <input type="text" class="label-opt" value="<?php print $cell_height ?>" id="label_cell_height" /> px
        </label>
        <label>Columns
                <input type="text" class="label-opt" value="<?php print $columns ?>" id="label_columns" />
        </label>
        <label>Right
                <input type="text" class="label-opt" value="<?php print $margin_right ?>" id="label_margin_right" /> px
        </label>
        <label>Bottom
                <input type="text" class="label-opt" value="<?php print $margin_bottom ?>" id="label_margin_bottom" /> px
        </label>
        <label>Font Size
                <input type="text" class="label-opt" value="<?php print $font_size ?>" id="label_font_size" /> pt
        </label>

        <label>Code Type
                <?php print form_dropdown('', array( 'barcode'=>'Barcode', 'qr'=>'QR Code' ), $code_type, 'id="label_code_type"'  ) ?>
        </label>

        <button id="label_refresh">refresh</button>
        <button id="label_default">make default</button>
    </div>
    <input type="hidden" value="" id="label_id" />
    <iframe id="label_frame" name="label_frame" width="100%" height="90%"
    marginWidth="0" marginHeight="0" frameBorder="0" scrolling="auto"
    title="Dialog Title">Your browser does not suppr</iframe>
</div>

