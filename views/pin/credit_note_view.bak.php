<?php if(isset($credit_note_data[0]["sale_order_no"])){?>
<div class="row-fluid sortable">
	<form method="post">
	<div class="panel panel-primary">
		<div class="panel-heading">View Credit Note</div>
		<div class="panel-body">
						<div class="col-md-6 form-group" title="saleorderno" data-rel="-">
						<label for="saleorderno">Sales Order Number</label>
						<span class="form-control"><?php echo (isset($credit_note_data[0]["sale_order_no"]))?$credit_note_data[0]["sale_order_no"]:"";?></span>
						</div>			

						<div class="col-md-6 form-group" title="customer" data-rel="-">
						<label for="customer">Customer</label>
						<span class="form-control"><?php echo (isset($credit_note_data[0]["customer_name"]))?$credit_note_data[0]["customer_name"]:"";?></span>
						</div>
						
						<div class="col-md-6 form-group" title="orderdate" data-rel="-">
						<label for="orderdate">Order Date</label>
						<span class="form-control"><?php echo (isset($credit_note_data[0]["sale_order_date"]))?date('d M Y',strtotime($credit_note_data[0]["sale_order_date"])):"";?></span>
						</div>
					
						<div class="col-md-6 form-group" title="invoice" data-rel="-">
						<label for="invoice">Invoice Number</label>
						<span class="form-control"><?php echo (isset($credit_note_data[0]["invoice_no"]))?$credit_note_data[0]["invoice_no"]:"";?></span>
						</div>
						
						<div class="col-md-6 form-group" title="creditnoteno" data-rel="-">
						<label for="creditnoteno">Credit Note Number</label>
						<span class="form-control"><?php echo (isset($credit_note_data[0]["credit_note_no"]))?$credit_note_data[0]["credit_note_no"]:"";?></span>
						</div>
						
						<div class="col-md-6 form-group" title="cntype" data-rel="-">
						<label for="cntype">Credit Note Type</label>
						<span class="form-control"><?php echo (isset($credit_note_data[0]["credit_type"]) && $credit_note_data[0]["credit_type"]=="2" )?"Partial Credit Note":"Full Credit Note";?></span>
						</div>
	</div>
	</div>
	
	<div class="panel panel-default">
		<div class="panel-heading">Order Details</div>
		<div class="panel-body">
				<table class="table table-primary table-hover table-bordered">
				<thead><tr><td width="30%">Product</td><td width="30%">Unit Price</td><td width="20%">Qty</td><td width="20%">Amount</td></tr></thead>
				<tbody id="placeholder">
				<?php
				if(isset($credit_note_data)){
					$total_amount = 0;
					foreach($credit_note_data as $key=>$value){
						$amount =($value["qty"]*$value["product_value"]);
						$total_amount += $amount;
						echo '<tr>'.
						'<td>'.$value["product_desc"].' | '.$value["product_code"].'</td>'.
						'<td>'.$value["product_value"].'</td>'.
						'<td>'.$value["qty"].'</td>'.
						'<td>'.number_format($amount,3,".",",").'</td>'.
						'</tr>';
						
					}
					echo '<tr>'.
						'<td colspan="3"><span class="pull-right">Subtotal</span></td>'.
						'<td>'.number_format($total_amount,3,".",",").'</td>'.
						'</tr>';
				}
					
				?>
				</tbody>
				<tfoot>
				<tr><td colspan="3"><span class="pull-right">Discount(<?php echo (isset($credit_note_data[0]["discount_rate"]))?$credit_note_data[0]["discount_rate"]:"0";?>%)</span></td><td><?php echo $discount = (isset($credit_note_data[0]["discount"]))?$credit_note_data[0]["discount"]:"0.00";?></td></tr>
				<tr><td colspan="3"><span class="pull-right">Total Amount</span></td><td><?php echo number_format($total_amount-$discount,"3",".",",");?></td></tr>
				</tfoot>
				</table>
				<div align="center">
					<a href="index.php?r=pin/listcn" class="btn btn-default">Back</a>
				</div>
		</div>
	</div>
	</form>
	
	<?php if(isset(Yii::$app->user->identity->id)){?>
	<?php if(Yii::$app->user->identity->role=="admin" || Yii::$app->user->identity->role=="finance"){?>
	<?php //if((isset($sale_order_data["create_user"]) && $sale_order_data["create_user"]!="") && Yii::$app->user->identity->username!=$sale_order_data["create_user"]){?>
	<div class="panel panel-info">
		<div class="panel-heading">Approval & Activate</div>
		<div class="panel-body">
			<div class="row">
				<?php if(isset($credit_note_data[0]["approval_status"]) && $credit_note_data[0]["approval_status"]=="0"){?>
				<div class="col-md-2" align="center">
				<form method="post"><input class="btn btn-success" type="submit" value="YES" onclick="return confirm('Are you confirm to proceed with this action?');"/><input type="hidden" name="approved" value="YES"/></form>
				</div>
				<div class="col-md-10" align="left" style="border-left:1px solid black">
				<form method="post">
				<input class="btn btn-warning" type="submit" value="NO" onclick="return confirm('Are you confirm to proceed with this action?');"/><br/><br/>
				<textarea class="form-control" name="approval_desc" cols="50" rows="3" placeholder="Remarks / Notes / Reasons"></textarea>
				</form>
				</div>
				<?php }else{
					echo '<div class="col-md-12">';
					if(isset($credit_note_data[0]["approval_status"]) && $credit_note_data[0]["approval_status"]=="1"){ echo '<span class="btn btn-success">APPROVED by '.$credit_note_data[0]["approval_user"]; }
					else if(isset($credit_note_data[0]["approval_status"]) && $credit_note_data[0]["approval_status"]=="-1"){ 
						echo '<span class="btn btn-warning">REJECTED by '.$credit_note_data[0]["approval_user"].'</span>'; 
						echo "<br/>";
						echo "<br/>";
						echo '<textarea class="form-control" disabled cols="50" rows="3">'.$credit_note_data[0]["approval_desc"].'</textarea>';
					} 
					echo '</div>';
				}
				?>
				
			</div>
		</div>
	</div>
	<?php } ?>
	<?php } ?>
</div>
<script type="text/javascript">

</script>
<?php }else{?>
					<div class="alert alert-info">
					  <strong>Info!</strong> Invalid Credit Note ID, please check with Administrator.<br/>
					  <a href="index.php?r=pin/listcn" class="btn btn-default">Back</a>
					</div>
<?php } ?>