<?php if(isset($sales_order_data["sale_order_no"])){?>
<div class="row-fluid sortable">
	<form method="post">
	<div class="panel panel-primary">
		<div class="panel-heading">View Sales Order</div>
		<div class="panel-body">
						<div class="col-md-6 form-group" title="saleorderno" data-rel="-">
						<label for="saleorderno">Sales Order Number</label>
						<span class="form-control"><?php echo (isset($sales_order_data["sale_order_no"]))?$sales_order_data["sale_order_no"]:"";?></span>
						</div>			

						<div class="col-md-6 form-group" title="customer" data-rel="-">
						<label for="customer">Customer</label>
						<span class="form-control"><?php echo (isset($sales_order_data["customer_name"]))?$sales_order_data["customer_name"]:"";?></span>
						</div>
						
						<div class="col-md-6 form-group" title="orderdate" data-rel="-">
						<label for="orderdate">Sales Order Date</label>
						<span class="form-control"><?php echo (isset($sales_order_data["sale_order_date"]))?date('d M Y',strtotime($sales_order_data["sale_order_date"])):"";?></span>
						</div>
					
						<?php if(isset($sales_order_data["dtu_batch"])){?>
						<div class="col-md-6 form-group" title="dtubatch" data-rel="-">
						<label for="orderdate">DTU Month</label>
						<span class="form-control"><?php echo $sales_order_data["dtu_batch"];?></span>
						</div>
						<?php } ?>
						
						<div class="col-md-6 form-group" title="invoice" data-rel="-">
						<label for="invoice">Invoice Number</label>
						<span class="form-control"><?php echo (isset($sales_order_data["invoice_no"]))?$sales_order_data["invoice_no"]:"";?></span>
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
				if(isset($sales_order_details)){
					$total_amount = 0;
					foreach($sales_order_details as $key=>$value){
						if($value["product_code"]=="TRX FEE")
						{
							$amount =($value["qty"]*$value["product_value"]);
							$total_amount -= $amount;
							echo '<tr>'.
							'<td>'.$value["product_desc"].' | '.$value["product_code"].'</td>'.
							'<td>'.$value["product_value"].'</td>'.
							'<td>'.$value["qty"].'</td>'.
							'<td>('.number_format($amount,3,".",",").')</td>'.
							'</tr>';
						}else if(isset($sales_order_data["dtu_batch"]) && $sales_order_data["dtu_batch"]!=""){
							$amount =($value["qty"]*$value["product_value"]);
							$total_amount += $amount;
							echo '<tr>'.
							'<td>'.$value["product_desc"].' | '.$value["product_code"].'</td>'.
							'<td>'.$value["product_value"].'</td>'.
							'<td>'.$value["qty"].'</td>'.
							'<td>'.number_format($amount,3,".",",").'</td>'.
							'</tr>';
						}else{
							$amount =($value["qty"]*$value["product_value"]);
							$total_amount += $amount;
							echo '<tr>'.
							'<td>'.$value["product_desc"].' | '.$value["product_code"].'<br/><span class="btn btn-info btn-xs" onclick="getList(this)" data-value="'.$sales_order_data["sale_order_id"].'" data-product="'.(substr($value["product_code"],0,3)).'">Card Detail</span><span class="loader"></span></td>'.
							'<td>'.$value["product_value"].'</td>'.
							'<td>'.$value["qty"].'</td>'.
							'<td>'.number_format($amount,3,".",",").'</td>'.
							'</tr>';
						}
					}
					echo '<tr>'.
						'<td colspan="3"><span class="pull-right">Subtotal</span></td>'.
						'<td>'.number_format($total_amount,3,".",",").'</td>'.
						'</tr>';
				}
					
				?>
				</tbody>
				<tfoot>
				<tr><td colspan="3"><span class="pull-right">Discount(<?php echo (isset($sales_order_data["discount_rate"]))?$sales_order_data["discount_rate"]:"0";?>%)</span></td><td><?php echo $discount = (isset($sales_order_data["discount"]))?$sales_order_data["discount"]:"0.00";?></td></tr>
				<?php if(count($credit_note_data)>0){?>
					<?php foreach($credit_note_data as $key=>$value){?>
					<?php $total_amount = $total_amount - $value["total_amount"];?>
					<tr><td colspan="3"><span class="pull-right">Less Credit <a target="_blank" href="index.php?r=pin/viewcn&id=<?php echo $value["credit_note_id"];?>">Credit Note</a></span></td><td><?php echo number_format($value["total_amount"],"3",".",",");?></td></tr>
					<?php } ?>
					<tr><td colspan="3"><span class="pull-right">Total Amount</span></td><td><?php echo number_format($total_amount-$discount,"3",".",",");?></td></tr>
				<?php }else{ ?>
				<tr><td colspan="3"><span class="pull-right">Total Amount</span></td><td><?php echo number_format($total_amount-$discount,"3",".",",");?></td></tr>
				<?php } ?>
				<tr><td colspan="3"><span class="pull-right">Payment Term</span></td><td><?php echo (isset($sales_order_data["term"]))?$sales_order_data["term"]." Days":"-";?></td></tr>
				<tr><td colspan="3"><span class="pull-right">Tax</span></td><td><?php echo (isset($sales_order_data["tax"]))?$sales_order_data["tax"]:"-";?></td></tr>
				</tfoot>
				</table>
				<div align="center">
					<a href="index.php?r=pin/export&id=<?php echo $_GET["id"];?>" class="btn btn-success">Get Pin List</a>
					<?php //if(Yii::$app->user->identity->role!="finance"){?>
					<?php if(1==1){?>
					<?php	if( (isset($sales_order_data["approval_status"]) && $sales_order_data["approval_status"]=="1")
							&& !isset($sales_order_data["dtu_batch"]) ){ ?>
						<a href="index.php?r=pin/cancelso&id=<?php echo $_GET["id"];?>" class="btn btn-info">Add Credit Note</a>
					<?php } ?>
					<?php } ?>
					<a href="index.php?r=pin/listso" class="btn btn-default">Back</a>
				</div>
		</div>
	</div>
	</form>
	<?php if($sales_order_data["discount_rate"]!=$sales_order_data["discount_rate_ori"]){?>
		<div class="alert alert-danger">
		  <strong style="font-size:3em;color:red">Warning!</strong><br/>
		  <h4>Special Discount rate applied, Default discount rate is <u><?php echo $sales_order_data["discount_rate_ori"];?>%</u></h4>
		</div>
	<?php } ?>
	<?php if(isset(Yii::$app->user->identity->id)){?>
	<?php if(Yii::$app->user->identity->role=="admin" || Yii::$app->user->identity->role=="finance"){?>
	<?php //if((isset($sale_order_data["create_user"]) && $sale_order_data["create_user"]!="") && Yii::$app->user->identity->username!=$sale_order_data["create_user"]){?>
	<div class="panel panel-info">
		<div class="panel-heading">Approval & Activate</div>
		<div class="panel-body">
			<div class="row">
				<?php if(isset($sales_order_data["approval_status"]) && $sales_order_data["approval_status"]=="0"){?>
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
					if(isset($sales_order_data["approval_status"]) && $sales_order_data["approval_status"]=="1"){ echo '<span class="btn btn-success">APPROVED by '.$sales_order_data["approval_user"]; }
					else if(isset($sales_order_data["approval_status"]) && $sales_order_data["approval_status"]=="-1"){ 
						echo '<span class="btn btn-warning">REJECTED by '.$sales_order_data["approval_user"].'</span>'; 
						echo "<br/>";
						echo "<br/>";
						echo '<textarea class="form-control" disabled cols="50" rows="3">'.$sales_order_data["approval_desc"].'</textarea>';
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
function getList(obj)
{ 
	if($(obj).next().html()=="")
	{
		id = $(obj).attr("data-value");
		product = $(obj).attr("data-product");
		$.post("index.php?r=pin/ajaxcarddetails",{"id":id,"product":product},function(data)
		{
			html = "";
			for(var i in data)
			{
				html += "<br/>"+ data[i].cardserial;
			}
			$(obj).next().html(html);
		},"json");
	}else
	{
		$(obj).next().html("");
	}
}
</script>
<?php }else{?>
					<div class="alert alert-info">
					  <strong>Info!</strong> Invalid Sales Order ID, please check with Administrator.<br/>
					  <a href="index.php?r=pin/listso" class="btn btn-default">Back</a>
					</div>
<?php } ?>