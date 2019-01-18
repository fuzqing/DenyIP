<?php
include 'common.php';
include 'header.php';
include 'menu.php';
?>
<style type="text/css">
    .row1{
		width:120px;float:left;
		display:block;
		margin-left:5px;
		margin-top:5px;
        color:#E74C3C;
    } 
   .row2{
	   width:120px;display:block;float:left;
	   margin-left:5px;margin-top:5px;
       color:#117A65;
   }

</style>

<div class="main">
    <div class="body container">
        <?php include 'page-title.php'; ?>
        <div class="colgroup typecho-page-main manage-metas">
          <input type="text" id="denyip" name="denyip" class="text" value='' /> 
          <button type="button" id="add-ip" class="btn btn-s">添加</button>
          <button type="button" id="delete-ip" class="btn btn-s">删除</button>
          <br><br>
          <div class="col-mb-12 col-tb-8" role="main" style="padding: 30px;float:left;background: #FFF;">
              <?php
              $getContents = file_exists(__DIR__.'/denyip.json') ? file_get_contents(__DIR__.'/denyip.json') : "{}";
              $denyips = json_decode($getContents,1);
              //$add_url = $options->index('/action/denyip?do=add');
             // $delete_url = $options->index('/action/denyip?do=delete');
             // $update_url = $options->index('/action/denyip?do=update');
              if(!empty($denyips)) { 
                $i=1;
                foreach ($denyips as $denyip){
                  $class = 'row'.($i%2+1);
                  echo ' <span class="'.$class.'">&nbsp;'.$denyip.'&nbsp;</span> ';
                  $i++;
                }
              } else {
                _e('还没有IP'); 
              }
              ?>
          </div>
        </div>
    </div>
</div>

<?php

include 'common-js.php';
?>
<script src="//cdn.bootcss.com/layer/3.1.0/layer.js"></script>
<script>
  $(document).ready(function(){

    $("#add-ip").click(function() {
      let denyip = $("#denyip").val();
      if(!denyip) {
        layer.msg('请填写IP地址', function(){});
        return false;
      } else {
        var load_index = layer.load(2, {time: 10*1000});
        $.post("<?php $options->index('/action/denyip?do=denyone')?>",{denyip:denyip} ,function(data) {
          layer.close(load_index);
          if(data.code == 0) {
            layer.msg(data.msg, function(){
              window.location.reload();
            });
          } else {
             layer.msg(data.msg, function(){});
          }
        },"json");
      }
    });
   
    $("#delete-ip").click(function() {
      let denyip = $("#denyip").val();
      if(!denyip) {
        layer.msg('请填写IP地址', function(){});
        return false;
      } else {
        var load_index = layer.load(2, {time: 10*1000});
        $.post("<?php $options->index('/action/denyip?do=delete')?>",{denyip:denyip} ,function(data) {
          layer.close(load_index);
          if(data.code == 0) {
            layer.msg(data.msg, function(){
              window.location.reload();
            });
          } else {
             layer.msg(data.msg, function(){});
          }
        },"json");
      }
    });
    
  });
</script>
<?php include 'footer.php'; ?>
