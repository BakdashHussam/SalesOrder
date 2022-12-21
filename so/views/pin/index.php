<h1>Sales Order System</h1>
<style type="text/css">
.buttonstyle{
	width:200px;
	height:200px;
	padding:30px;
	margin:20px
}
</style>
<div class="row-fluid sortable">
    <div class="box span12">
		<?php if(Yii::$app->user->identity->role!="finance"){ ?>
		<a href="index.php?r=pin/createso" class="btn btn-primary buttonstyle"><i class="glyphicon glyphicon-book" style="font-size:8em;"></i><br/>Create Sale Order</a>
		<a href="index.php?r=pin/getpendingdtu" class="btn btn-primary buttonstyle"><i class="glyphicon glyphicon-folder-open" style="font-size:8em;"></i><br/>DTU Pending List</a>
		<a href="javascript:createCN()" class="btn btn-primary buttonstyle"><i class="glyphicon glyphicon-file" style="font-size:8em;"></i><br/>Create Credit Note</a>
		<?php } ?>
		
		<a href="index.php?r=pin/listso" class="btn btn-primary buttonstyle"><i class="glyphicon glyphicon-list-alt" style="font-size:8em;"></i><br/>Sale Order List</a>
		<a href="index.php?r=pin/listcn" class="btn btn-primary buttonstyle"><i class="glyphicon glyphicon-th-list" style="font-size:8em;"></i><br/>Credit Note List</a>
		<a href="index.php?r=pin/checkcardstatus" class="btn btn-primary buttonstyle"><i class="glyphicon glyphicon-search" style="font-size:8em;"></i><br/>Check Card Status</a>
		<a href="index.php?r=pin/setting" class="btn btn-primary buttonstyle"><i class="glyphicon glyphicon-cog" style="font-size:8em;"></i><br/>Setting</a>
    </div>
</div>





<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
	<form action="index.php?r=pin/getsoforcn" method="post">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Add Credit Note</h4>
      </div>
      <div class="modal-body">
		
		<div class="form-group">
        <input class="form-control" type="text" name="sale_order_no" placeholder="Enter Sales Order Number here if available"/>
		</div>
		<div class="form-group">
		<input class="form-control" type="text" name="invoice_no" placeholder="Enter Invoice Number here if available"/>
		</div>
      </div>
      <div class="modal-footer">
        <input type="submit" class="btn btn-primary" value="Add"/>
		<span class="btn btn-default" data-dismiss="modal">Close</span>
      </div>
    </div>
	</form>
  </div>
</div>

<script type="text/javascript">
function createCN()
{
	$('#myModal').modal();
}
</script>