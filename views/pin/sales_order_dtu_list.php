<div class="row-fluid sortable">
    <div class="panel panel-primary">
		<div class="panel-heading">Sales Order DTU Pending List</div>
		<div class="panel-body">
		<?php if(isset($result) && !empty($result)){  ?>
		
		<?php
			$pgw_list = array();
			foreach($customerlist as $key=>$value){	$pgw_list[strtolower($value["customer_code"])] = $value;	}
		?>
		
		<table class="table table-primary table-hover table-bordered">
		<thead>
		<tr>
			<td>DTU Date</td>
			<td>Customer</td>
			<td>Currency</td>
			<td>Amount</td>
			<td>Action</td>
		</tr>
		</thead>
		<tbody>
		<?php foreach($result as $key=>$value){?>
		<tr>
			<td><?php echo $value["theDate"];?></td>
			<td><?php echo isset($pgw_list[$value["customer"]])?$pgw_list[$value["customer"]]["customer_name"]:"";?></td>
			<td><?php echo isset($pgw_list[$value["customer"]])?$pgw_list[$value["customer"]]["currency"]:"";?></td>
			<td><?php echo number_format($value["ttl"],"3",".",",");?></td>
			<td>
			<span class="btn btn-primary btn-xs btn-create" onclick="postData(this);" 
			data-date="<?php echo $value["theDate"];?>" 
			data-currency="<?php echo isset($pgw_list[$value["customer"]])?$pgw_list[$value["customer"]]["currency"]:"";?>" 
			data-customer="<?php echo isset($pgw_list[$value["customer"]])?$pgw_list[$value["customer"]]["customer_code"]:"";?>"
			data-customer-id="<?php echo isset($pgw_list[$value["customer"]])?$pgw_list[$value["customer"]]["customer_id"]:"";?>"
			data-customer-name="<?php echo isset($pgw_list[$value["customer"]])?$pgw_list[$value["customer"]]["customer_name"]:"";?>"
			data-discount="<?php echo isset($pgw_list[$value["customer"]])?$pgw_list[$value["customer"]]["discount"]:"0";?>"
			data-term="<?php echo isset($pgw_list[$value["customer"]])?$pgw_list[$value["customer"]]["term"]:"0";?>"
			">
			Create
			</span>
			</td>
		<tr>
		<?php } ?>
		</tbody>
		</table>
		<?php }else{ ?>
		<h4>No Record(s)!</h4>
		<?php } ?>
		</div>
	</div>
	<div class="col-md-12">
		<div align="center">
			<a href="index.php?r=pin/index" class="btn btn-default">Back</a>
		</div>
	</div>
</div>
<form name="frm" method="post" action="index.php?r=pin/createdtuso">
<input type="hidden" name="customer"/>
<input type="hidden" name="customer_id"/>
<input type="hidden" name="customer_name"/>
<input type="hidden" name="currency"/>
<input type="hidden" name="dtu_month"/>
<input type="hidden" name="discount"/>
<input type="hidden" name="term"/>
</form>
<script type="text/javascript">
function postData(obj)
{
	$(".btn-create").hide();
	
	$("input[name=customer]").val($(obj).attr("data-customer"));
	$("input[name=customer_id]").val($(obj).attr("data-customer-id"));
	$("input[name=customer_name]").val($(obj).attr("data-customer-name"));
	$("input[name=currency]").val($(obj).attr("data-currency"));
	$("input[name=dtu_month]").val($(obj).attr("data-date"));
	$("input[name=discount]").val($(obj).attr("data-discount"));
	$("input[name=term]").val($(obj).attr("data-term"));
	$(obj).parent().html("Loading...");
	document.frm.submit();
}
</script>
