<?php if(isset($msg) && $msg!=""){?>
	<div class="alert alert-warning">
	  <strong>Warning!</strong> <?php echo $msg;?><br/>
	  <?php 
		if(isset($unmatch_item) && count($unmatch_item)>0)
		{
			foreach($unmatch_item as $key=>$value){
			  echo "Product Code <strong>".$key."</strong> are <strong>".$value."</strong> Card(s) different!<br/>";
			}
		}?>
	</div>
<?php } ?>
<?php if(isset($checker[0]["sale_order_no"])){?>
<div class="row-fluid sortable">
	<form method="post" enctype="multipart/form-data">
	<div class="panel panel-primary">
		<div class="panel-heading">Create Credit Note</div>
		<div class="panel-body">

						<div class="col-md-6 form-group" title="saleorderno" data-rel="-">
						<label for="saleorderno">Sales Order Number</label>
						<span class="form-control"><?php echo (isset($checker[0]["sale_order_no"]))?$checker[0]["sale_order_no"]:"";?></span>
						</div>			

						<div class="col-md-6 form-group" title="customer" data-rel="-">
						<label for="customer">Customer</label>
						<span class="form-control"><?php echo (isset($checker[0]["customer_name"]))?$checker[0]["customer_name"]:"";?></span>
						</div>
						
						<div class="col-md-6 form-group" title="orderdate" data-rel="-">
						<label for="orderdate">Order Date</label>
						<span class="form-control"><?php echo (isset($checker[0]["sale_order_date"]))?date('d M Y',strtotime($checker[0]["sale_order_date"])):"";?></span>
						</div>
					
						<div class="col-md-6 form-group" title="invoice" data-rel="-">
						<label for="invoice">Invoice Number</label>
						<span class="form-control"><?php echo (isset($checker[0]["invoice_no"]))?$checker[0]["invoice_no"]:"";?></span>
						</div>
						
						<div class="col-md-6 form-group" title="creditnoteno" data-rel="-">
						<label for="creditnoteno">Credit Note Number</label>
						<span class="form-control">SYSTEM GENERATE</span>
						</div>
						
						<div class="col-md-6 form-group" title="cntype" data-rel="-">
						<label for="cntype">Credit Note Type</label>
						<select class="form-control" name="creditnote_type" id="creditnote_type">
						<?php if(!isset($checker[0]["credited"])){?>
						<option value="1" selected>Full Credit Note</option>
						<?php } ?>
						<option value="2">Partial Credit Note</option>
						</select>
						</div>
						
						<div class="col-md-6 form-group" title="uploadfile" data-rel="-">
						<label for="creditnoteno">Upload File ( For Partial Credit only)</label>
						<input type="file" name="upload" id="upload"/><br/>
						<a target="_blank" href="index.php?r=pin/checkcardstatus" class="btn btn-info">Check Card Status here!</a>
						</div>
						
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">Order Details</div>
		<div class="panel-body">
				<?php 
				if(isset($checker))
				{
					$checkCount = count($checker);
					$total_amount = 0;

						if(isset($value["empty"]))
						{
							$checkCount = $checkCount-1;
						}
				}?>
						
				
				<table class="table table-primary table-hover table-bordered">
				<thead><tr><td width="30%">Product</td><td width="30%">Unit Price</td><td width="20%">Qty</td><td width="20%">Amount</td></tr></thead>
				<tbody id="placeholder">
				<?php
				$tax = $checker[0]["tax"];
				$total_amount = 0;
				$taxed = 0;
				$tax_disc = 0;
				$total_tax = 0;
				$discount = (isset($checker[0]["discount"]))?$checker[0]["discount"]:"0.00"; 
				if(isset($checker))
				{
					foreach($checker as $key=>$value)
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
						'<td>'.$value["product_desc"].' | '.$value["product_code"].'<input type="hidden" name="item[product_code][]" value="'.$value["product_code"].'"/><input type="hidden" name="item[product_desc][]" value="'.$value["product_desc"].'"/></td>'.
						'<td>'.$value["product_value"].'<input type="hidden" name="item[unitprice][]" value="'.$value["product_value"].'"/><input type="hidden" name="item[tax][]" value="'.$value["product_tax"].'"/></td>'.
						'<td><input type="number" class="cn_qty" name="item[qty][]" value="'.$value["qty"].'" min="0" max="'.$value["qty"].'" onchange="calculator()"/></td>'.
						'<td>'.number_format($amount,2,".",",").'</td>'.
						'</tr>';
						
					}
				}
					
				?>
				</tbody>
				<tfoot>
				<tr><td colspan="3"><span class="pull-right">Discount(<?php echo (isset($checker[0]["discount_rate"]))?$checker[0]["discount_rate"]:"0";?>%)</span></td>
					<td id="cal_disc_amount"></td>
				</tr>
				<tr>
					<td colspan="3"><span class="pull-right">Sub Total</span></td>
					<td class="total_amount"></td>
				</tr>
				<tr>
					<td colspan="3"><span class="pull-right">Total Tax</span></td>
					<td class="total_tax_amount"></td>
				</tr>
				
				<tr><td colspan="3"><span class="pull-right">Grand Total</span></td><td id="grand_total_amount"></td></tr>
				<tr><td colspan="3"><span class="pull-right">Tax</span></td><td><?php echo (isset($checker[0]["tax"]))?$checker[0]["tax"]:"-";?></td></tr>
				</tfoot>
				</table>
				
				<?php if($checkCount<=0){ ?>
					<div class="alert alert-info">
					  <strong>Info!</strong> No available balance to be credit.<br/>
					</div>
				<?php } ?>
				<div align="center">
					<?php if($checkCount>0){ ?><input type="submit" class="btn btn-primary" name="submit" value="Submit" onclick="return checknumber();"/><?php } ?>
					<input type="hidden" name="tax" value="<?php echo (isset($checker[0]["tax"]))?$checker[0]["tax"]:"-";?>"/>
					<input type="hidden" id="theDisc" value="<?php echo (isset($checker[0]["discount"]))?$checker[0]["discount"]:"0.00";?>" name="discount"/>
					<input type="hidden" id="theTotal" value="<?php echo $total_amount-$discount;?>" name="total_amount"/>
					<a href="index.php?r=pin/listso" class="btn btn-default">Back</a>
				</div>
		</div>
	</div>
	</form>
</div>
<script type="text/javascript">
function calculator()
{
	var ttl = 0;
	var gttl = 0;
	var tax_ttl = 0;
	var discount_amount = 0;
	var disc = parseFloat('<?php echo  $checker[0]["discount_rate"];?>');
	var taxMode = '<?php echo $checker[0]["tax"];?>';
	var taxDisc = 0;
	tax =  parseFloat('<?php echo $checker[0]["product_tax"];?>');
	$("table tbody tr").each(function(index){
		row = $(this).children();
		unitprice = parseFloat(row.eq(1).text());
		qty = row.eq(2).find('input').val();
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
		
		row.eq(3).text(grandAmount.toFixed(2));
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
	actual_disc = actual_total = 0;
	$("table tbody tr").each(function(index){
		row = $(this).children();
		unitprice = parseFloat(row.eq(1).text());
		qty = row.eq(2).find('input').val();
		total_amount = unitprice*qty;
		
		if(parseFloat(disc)>0)
		{	
			actual_total = actual_total+total_amount;
			this_discount = total_amount*(disc/100);
			actual_disc = actual_disc + this_discount;
		}
		else
		{
			actual_total = actual_total+total_amount;
		}
	});
	
	$("#theDisc").val(actual_disc);
	$("#theTotal").val(actual_total);
}

function checknumber()
{
	qty = $(".cn_qty");
	qtychecker = qty.length;
	answer = true;
	checker = 0;
	if($("#creditnote_type").val()=="2")
	{
		$(".cn_qty").each(function( index ) {
			min = $( this ).attr("min");
			max = $( this ).attr("max");
			if(min <= $( this ).val() && max >=$( this ).val())
			{
				if($( this ).val()=="0")
				{
					qtychecker--;
				}
			}
			else
			{
				$( this ).val(max);
				$( this ).css("border","1px solid red");
				$( this ).parent().append('<br/><span style="color:red">Quantity must not less than <strong>'+min+'</strong> and must not more than <strong>'+max+'</strong></span>');
				checker++;
			}
			
		});
		calculator();
	}
	else
	{
		$(".cn_qty").each(function( index ) {
			max = $( this ).attr("max");
			$( this ).val(max);
		});
		calculator();
		answer = confirm("Are you confirm to proceed with FULL credit?");
	}
	if(qtychecker==0)
	{
		alert("Not allow to have all quantity as 0!");
		answer = false;
	}
	//return false;
	return (qtychecker>0 && checker==0 && answer)?confirm('Are you sure to proceed with this action?'):false;
}
function fn(number)
{
	return new Intl.NumberFormat('en-IN', { style: 'decimal', minimumFractionDigits : '3' }).format(number)
}
window.onload = function(){ calculator()};

</script>
<?php }else{?>
					<div class="alert alert-info">
					  <strong>Info!</strong> Invalid Sales Order ID, please check with Administrator.<br/>
					  <a href="index.php?r=pin/listso" class="btn btn-default">Back</a>
					</div>
<?php } ?>