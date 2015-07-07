
<form method="post" action="<?php echo site_url('admin/awbimport/commit')?>">

    <input type="submit" value="Commit" name="commit" />

    <?php
        print anchor('admin/awbimport','Cancel');
    ?>

    <h3>Logistic ID : <?php print $merchant_id ?></h3>
    <h3>Logistic Name : <?php print $merchant_name ?></h3>
    <h4>Post Update Process</h4>
    <h3>Update Status to : <?php print $update_status ?></h3>

    <input type="hidden" value="<?= $merchant_name; ?>" name="merchant_name" >
    <input type="hidden" value="<?= $merchant_id; ?>" name="merchant_id" >
    <input type="hidden" value="<?= $update_status; ?>" name="update_status" >
    <input type="hidden" value="<?= $jsonfile; ?>" name="jsonfile" >

    <?php foreach ($tables as $name => $table): ?>
        <div class="table">
            <?php print $table ?>
        </div>
    <?php endforeach ?>

</form>

<script type="text/javascript">

$(document).ready(function(){
        $('#select_all').click(function(){
            if($('#select_all').is(':checked')){
                $('.selector').attr('checked', true);
            }else{
                $('.selector').attr('checked', false);
            }
        });
});

</script>