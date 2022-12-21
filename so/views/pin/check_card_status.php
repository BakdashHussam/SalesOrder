<?php if(isset($msg) && $msg!=""){?>
	<div class="alert alert-warning">
	  <strong>Warning!</strong> <?php echo $msg;?><br/>
	</div>
<?php } ?>
<h1>Check Cubicard Status</h1>

<div class="row">
	<div class="col-md-6">
		<div class="panel panel-default">
		<div class="panel-heading">Search By</div>
		<div class="panel-body">
		<form name="frm_check" method="post">
			<div class="form-group">
			<input class="form-control" type="text" id="sale_order_no" name="sale_order_no" placeholder="Enter Sales Order Number here if available"/>
			</div>
			<div class="form-group">
			<input class="form-control" type="text" id="invoice_no" name="invoice_no" placeholder="Enter Invoice Number here if available"/>
			</div>
			<input class="btn btn-primary" type="submit" value="Go" name="submit" onclick="return checkfield();">
			<input type="hidden" name="go" value="1"/>
			<a href="index.php?r=pin/index" class="btn btn-default">Back</a>
		</form>
		</div>
		</div>
  </div>
  <div class="col-md-6">
	  <div class="panel panel-default">
		<div class="panel-heading">File Upload Check</div>
		<div class="panel-body">
			<form name="frm_upload" method="post" enctype="multipart/form-data">
			<input type="file" name="upload" id="upload"><br/>
			<input class="btn btn-primary" type="submit" value="Check Now" name="submit">
			<a href="index.php?r=pin/index" class="btn btn-default">Back</a>
			</form>
		</div>
	  </div>
  </div>
  
  <div class="col-md-6">
	  <div class="panel panel-default">
		<div class="panel-heading">Single Card Check</div>
		<div class="panel-body">
			<form name="frm_check" method="post">
			<div class="form-group">
			<input class="form-control" type="text" id="cardserial" name="cardserial" placeholder="Enter cardserial"/>
			</div>
			<input class="btn btn-primary" type="submit" value="Go" name="submit" onclick="return checkfield2();">
			<input type="hidden" name="checksingle" value="1"/>
			<a href="index.php?r=pin/index" class="btn btn-default">Back</a>
			</form>
		</div>
	  </div>
  </div>
</div>

<hr/>
<?php if(isset($checklist) && is_array($checklist)){ ?>
<?php if(count($checklist)>0){ ?>
<div class="table-responsive">
<table class="table table-primary table-hover table-bordered">
<thead>
<tr>
	<th>Product Code</th>
	<th>Cardserial</th>
	<th>Cardcode</th>
	<th>SaleOrder No</th>
	<th>Invoice No</th>
	<th>Player ID</th>
	<th>Player Name</th>
	<th>Player IP</th>
	<th>Used Date</th>
	<th>Blocked?</th>
</tr>
</thead>
<tbody>
	<?php foreach($checklist as $key=>$value){ ?>
		<tr>
			<td><?php echo $value["batchid"];?></td>
			<td><?php echo $value["cardserial"];?></td>
			<td><?php echo $value["cardcode"];?></td>
			<td><?php echo $value["remarks"];?></td>
			<td><?php echo $value["invoice_no"];?></td>
			<td><?php echo $value["ownerid"];?></td>
			<td><?php echo $value["ownermemberid"];?></td>
			<td><?php echo $value["ownerip"];?></td>
			<td><?php echo ($value["used_date"]!="")?date("d M Y H:i:s",strtotime($value["used_date"])):"";?></td>
			<td><?php echo ($value["blocked"]=="0")?"No":"Yes";?></td>
		</tr>
	<?php } ?>
</tbody>
</table>
</div>
<?php }else{ ?>
<h3>No record found.</h3>
<?php } ?>
<?php } ?>

<script type="text/javascript">
function checkfield()
{
	inv_no = $("#invoice_no").val();
	sal_no = $("#sale_order_no").val();
	if(inv_no != "" || sal_no!="")
	{
		return true;
	}
	else{
		alert("Please fill in atleast one field");
		$("#invoice_no").css("border","1px solid red");
		$("#sale_order_no").css("border","1px solid red");
		return false;
	}
}

function checkfield2()
{
	a = $("#cardserial").val();
	if(a != "")
	{
		return true;
	}
	else{
		alert("Please fill in atleast one field");
		$("#cardserial").css("border","1px solid red");
		return false;
	}
}
</script>