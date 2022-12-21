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
				?>
				<table class="table table-primary table-hover table-bordered">
				<thead><tr><td width="30%">Product</td><td width="30%">Unit Price</td><td width="20%">Qty</td><td width="20%">Amount</td></tr></thead>
				<tbody id="placeholder">
				<?php
					foreach($checker as $key=>$value){
						if(isset($value["empty"]))
						{
							$checkCount = $checkCount-1;
						}
						else
						{
							$amount =($value["qty"]*$value["product_value"]);
							$total_amount += $amount;
							echo '<tr>'.
							'<td>'.$value["product_desc"].' | '.$value["product_code"].'<input type="hidden" name="item[product_code][]" value="'.$value["product_code"].'"/><input type="hidden" name="item[product_desc][]" value="'.$value["product_desc"].'"/></td>'.
							'<td>'.$value["product_value"].'<input class="cn_unitprice" type="hidden" name="item[unitprice][]" value="'.$value["product_value"].'"/></td>'.
							'<td><input type="number" class="cn_qty" name="item[qty][]" value="'.$value["qty"].'" min="0" max="'.$value["qty"].'" onchange="calculate()"/></td>'.
							'<td class="cn_amount">'.number_format($amount,3,".",",").'</td>'.
							'</tr>';
						}
					}
					if($checkCount>0)
					{
					echo '<tr>'.
						'<td colspan="3"><span class="pull-right">Subtotal</span></td>'.
						'<td id="subttl">'.number_format($total_amount,3,".",",").'</td>'.
						'</tr>';
					}
				} // end if checker
				?>
				</tbody>
				<?php if($checkCount>0){ ?>
				<tfoot>
				<tr><td colspan="3"><span class="pull-right">Discount(<?php echo (isset($checker[0]["discount_rate"]))?$checker[0]["discount_rate"]:"0";?>%)</span></td><td id="discount"><?php echo $discount = (isset($checker[0]["discount"]))?$checker[0]["discount"]:"0.00";?></td></tr>
				<tr><td colspan="3"><span class="pull-right">Total Amount</span></td><td id="ttl"><?php echo number_format($total_amount-$discount,"3",".",",");?></td></tr>
				<tr><td colspan="3"><span class="pull-right">Payment Term</span></td><td><?php echo (isset($checker[0]["term"]))?$checker[0]["term"]." Days":"-";?></td></tr>
				<tr>
					<td colspan="3">
						<span class="pull-right">Tax</span>
					</td>
					<td><?php echo (isset($checker[0]["tax"]))?$checker[0]["tax"]:"-";?>
						<input type="hidden" value="<?php echo $discount;?>" name="discount"/>
						<input type="hidden" value="<?php echo number_format($total_amount-$discount,"3",".",",");?>" name="total_amount"/>
					</td>
				</tr>
				</tfoot>
				<?php } ?>
				</table>
				<?php if($checkCount<=0){ ?>
					<div class="alert alert-info">
					  <strong>Info!</strong> No available balance to be credit.<br/>
					</div>
				<?php } ?>
				<div align="center">
					<?php if($checkCount>0){ ?><input type="submit" class="btn btn-primary" name="submit" value="Submit" onclick="return checknumber();"/><?php } ?>
					<a href="index.php?r=pin/listso" class="btn btn-default">Back</a>
				</div>
		</div>
	</div>
	</form>
</div>
<script type="text/javascript">
function calculate()
{ 
	discount_rate = '<?php echo (isset($checker[0]["discount_rate"]))?$checker[0]["discount_rate"]:"0";?>';
	discount= 0;
	ttl=0;
	unitprice = $(".cn_unitprice");
	qty = $(".cn_qty");
	amount = $(".cn_amount");
	counter = qty.length;
	for(i=0;i<counter;i++)
	{
		cal = parseFloat($(unitprice[i]).val()) * parseFloat($(qty[i]).val());
		ttl += cal;
		$(amount[i]).html(fn(cal));
	}
	if(discount_rate>0)
	{
		discount = (parseFloat(ttl)*parseFloat(discount_rate))/100;
		$("#discount").html(fn(discount));
		$("input[name=discount]").val(fn(discount));
	}
	$("#subttl").html(fn(ttl));
	$("#ttl").html(fn(ttl-discount));
	$("input[name=total_amount]").val(fn(ttl-discount));
	
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
		calculate();
	}
	else
	{
		$(".cn_qty").each(function( index ) {
			max = $( this ).attr("max");
			$( this ).val(max);
		});
		calculate();
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
window.onload = function(){ calculate()};

</script>
<?php }else{?>
					<div class="alert alert-info">
					  <strong>Info!</strong> Invalid Sales Order ID, please check with Administrator.<br/>
					  <a href="index.php?r=pin/listso" class="btn btn-default">Back</a>
					</div>
<?php } ?>