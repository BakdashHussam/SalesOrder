<?php if(isset($sales_order_data["sale_order_no"])){?>
<div class="row-fluid sortable">
	<form method="post">
	<div class="panel panel-primary">
		<div class="panel-heading">Edit Sales Order</div>
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
						<label for="orderdate">Order Date</label>
						<span class="form-control"><?php echo (isset($sales_order_data["sale_order_date"]))?date('d M Y',strtotime($sales_order_data["sale_order_date"])):"";?></span>
						</div>
					
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
						$amount =($value["qty"]*$value["product_value"]);
						$total_amount += $amount;
						echo '<tr>'.
						'<td>'.$value["product_desc"].' | '.$value["product_code"].'<br/><span class="btn btn-info btn-xs" onclick="getList(this)" data-value="'.$sales_order_data["sale_order_id"].'" data-product="'.(substr($value["product_code"],0,3)).'">Card Detail</span><span class="loader"></span></td>'.
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
				<tr><td colspan="3"><span class="pull-right">Discount(<?php echo (isset($sales_order_data["discount_rate"]))?$sales_order_data["discount_rate"]:"0";?>%)</span></td><td><?php echo $discount = (isset($sales_order_data["discount"]))?$sales_order_data["discount"]:"0.00";?></td></tr>
				<tr><td colspan="3"><span class="pull-right">Total Amount</span></td><td><?php echo number_format($total_amount-$discount,"3",".",",");?></td></tr>
				<tr><td colspan="3"><span class="pull-right">Payment Term</span></td><td><?php echo (isset($sales_order_data["term"]))?$sales_order_data["term"]." Days":"-";?></td></tr>
				<tr><td colspan="3"><span class="pull-right">Tax</span></td><td><?php echo (isset($sales_order_data["tax"]))?$sales_order_data["tax"]:"-";?></td></tr>
				</tfoot>
				</table>
				<div align="center">
					<a href="index.php?r=pin/export&id=<?php echo $_GET["id"];?>" class="btn btn-success">Get Pin List</a>
					<!--a href="index.php?r=pin/editso&id=<?php echo $_GET["id"];?>" class="btn btn-primary">Update Invoice Number</a-->
					<a href="index.php?r=pin/listso" class="btn btn-default">Back</a>
				</div>
		</div>
	</div>
	</form>

</div>
<script type="text/javascript">
function getList(obj)
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
}
</script>
<?php }else{?>
					<div class="alert alert-info">
					  <strong>Info!</strong> Invalid Sales Order ID, please check with Administrator.<br/>
					  <a href="index.php?r=pin/listso" class="btn btn-default">Back</a>
					</div>
<?php } ?>