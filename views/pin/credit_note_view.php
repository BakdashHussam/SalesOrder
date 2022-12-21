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
				$tax = $credit_note_data[0]["tax"];
				$total_amount = 0;
				$taxed = 0;
				$tax_disc = 0;
				$total_tax = 0;
				$discount = (isset($credit_note_data[0]["discount"]))?$credit_note_data[0]["discount"]:"0.00"; 
				if(isset($credit_note_data))
				{
					foreach($credit_note_data as $key=>$value)
					{
						$amount =($value["qty"]*$value["product_value"]);
						if($tax=="Inclusive")
						{
							$taxed = ($amount*$value["product_tax"])/(100+$value["product_tax"]);
							$amount = $amount-$taxed;
							$total_tax = $total_tax + $taxed;
						}
						else if($tax=="Exclusive")
						{
							$taxed = ($amount*$value["product_tax"])/100;
							$total_tax = $total_tax + $taxed;
						}
						$total_amount += $amount;
						echo '<tr>'.
						'<td>'.$value["product_desc"].' | '.$value["product_code"].'</td>'.
						'<td>'.$value["product_value"].'</td>'.
						'<td>'.$value["qty"].'</td>'.
						'<td>'.number_format($amount,2,".",",").'</td>'.
						'</tr>';
						
					}
				}
					
				?>
				</tbody>
				<tfoot>
				<tr><td colspan="3"><span class="pull-right">Discount(<?php echo (isset($credit_note_data[0]["discount_rate"]))?$credit_note_data[0]["discount_rate"]:"0";?>%)</span></td>
					<td>
					<?php 
					if($tax=="Inclusive")
					{
						$discount_amount = (($discount*$value["product_tax"])/(100+$value["product_tax"]));
						$discount = $discount - $discount_amount;
						$total_tax = $total_tax - $discount_amount;
					}
					else if($tax=="Exclusive")
					{
						$discount_amount = (($discount*$value["product_tax"])/100);
						$discount = $discount;
						$total_tax = $total_tax - $discount_amount ;
					}
					echo "(".number_format($discount,"2",".",",").")";
					?>
					</td>
				</tr>
				<tr>
					<td colspan="3"><span class="pull-right">Sub Total</span></td>
					<td><?php echo number_format($total_amount-$discount,2,".",",");?></td>
				</tr>
				<tr>
					<td colspan="3"><span class="pull-right">Total Tax</span></td>
					<td><?php echo number_format($total_tax,2,".",",");?></td>
				</tr>
				
				<tr><td colspan="3"><span class="pull-right">Grand Total</span></td><td><?php echo number_format($total_amount-$discount+$total_tax,"2",".",",");?></td></tr>
				<tr><td colspan="3"><span class="pull-right">Tax</span></td><td><?php echo (isset($credit_note_data[0]["tax"]))?$credit_note_data[0]["tax"]:"-";?></td></tr>
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
window.onload = function(){
	calculator();
};

function calculator()
{
	var ttl = 0;
	var gttl = 0;
	var tax_ttl = 0;
	var discount_amount = 0;
	var disc = parseFloat($("input[name=discount]").val());
	var taxMode = $("select[name=tax] option:selected").val();
	var taxDisc = 0;
	tax = parseFloat($("#tax_rate").val());
	$("table tbody tr").each(function(index){
		row = $(this).children();
		unitprice = parseFloat(row.eq(2).text());
		qty = row.eq(3).text();
		
		total_amount = unitprice*qty;
		
		if(taxMode=="Inclusive"){
			
			if(parseFloat(disc)>0)
			{
				taxed = parseFloat(((total_amount*tax)/(100+tax)));
				grandAmount = total_amount-taxed;
				this_discount = grandAmount*(disc/100);
				discount_amount = discount_amount +this_discount;
				tax_disc = taxed*(disc/100);
				taxDisc = taxDisc+tax_disc;
			}
			else
			{
				taxed = parseFloat(((total_amount*tax)/(100+tax)));
				grandAmount = total_amount-taxed;
			}
			
		}else{
			if(parseFloat(disc)>0)
			{
				this_discount = total_amount*(disc/100);
				grandAmount = total_amount;
				taxed = parseFloat( (((total_amount-this_discount)*tax)/100) );
				discount_amount = discount_amount + this_discount;
			}
			else
			{
				grandAmount = total_amount;
				taxed = parseFloat(((grandAmount*tax)/100));
			}
		}
		
		row.eq(4).text(grandAmount.toFixed(2));
		ttl = ttl + grandAmount;
		gttl = gttl + total_amount;
		tax_ttl = tax_ttl + taxed;
	});

	if(taxMode=="Inclusive"){
		$(".total_tax_amount").html((tax_ttl-taxDisc).toFixed(2));
		$(".total_amount").html((ttl-discount_amount).toFixed(2));
		addtogrand = 0;
	}else{
		$(".total_tax_amount").html((tax_ttl).toFixed(2));
		$(".total_amount").html((ttl-discount_amount).toFixed(2));
		addtogrand = tax_ttl;
	}
	if(ttl>0)
	{
		$("#cal_disc_amount").text("("+discount_amount.toFixed(2)+")");
		$("#grand_total_amount").text(((gttl-discount_amount-taxDisc)+addtogrand).toFixed(2));
	}
}

</script>
<?php }else{?>
					<div class="alert alert-info">
					  <strong>Info!</strong> Invalid Credit Note ID, please check with Administrator.<br/>
					  <a href="index.php?r=pin/listcn" class="btn btn-default">Back</a>
					</div>
<?php } ?>