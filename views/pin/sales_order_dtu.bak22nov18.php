<?php if(!isset($no_data)){?>
<div class="row-fluid sortable">
	<form method="post" id="frm" name="frm">
	<div class="panel panel-primary">
		<div class="panel-heading">Create DTU Sales Order</div>
		<div class="panel-body">

						<div class="col-md-6 form-group" title="saleorderno" data-rel="-">
						<label for="saleorderno">Sales Order Number</label>				    				    
							<input class="form-control" disabled type="text" name="saleorderno" value="SYSTEM GENERATE"/>
						</div>			

						<div class="col-md-6 form-group" title="customer" data-rel="-">
						<label for="customer">Customer</label>				    				    
						<span class="form-control"><?php echo isset($_POST["customer_name"])?$_POST["customer_name"]:"";?></span>
						<input type="hidden" name="customer" value="<?php echo isset($_POST["customer"])?$_POST["customer"]:"";?>"/>
						<input type="hidden" name="orderdate" value="<?php echo date("Y-m-d",strtotime("-1 day", strtotime(date("1-M-Y"))));?>"/>
						<input type="hidden" name="customer_id" value="<?php echo isset($_POST["customer_id"])?$_POST["customer_id"]:"";?>"/>
						<input type="hidden" name="customer_name" value="<?php echo isset($_POST["customer_name"])?$_POST["customer_name"]:"";?>"/>
						<input type="hidden" name="currency" value="<?php echo isset($_POST["currency"])?$_POST["currency"]:"";?>"/>
						<input type="hidden" name="discount_ori" value="<?php echo isset($_POST["discount"])?$_POST["discount"]:"";?>"/>
						<input type="hidden" name="dtu_month" value="<?php echo isset($_POST["dtu_month"])?$_POST["dtu_month"]:"";?>"/>
						</div>
						
						<div class="col-md-6 form-group" title="createdate" data-rel="-">
						<label for="orderdate">Sales Order Date</label>				    
						<span class="form-control"><?php echo date("d-M-Y",strtotime("-1 day", strtotime(date("1-M-Y"))));?></span>
						</div>
						
						<div class="col-md-6 form-group" title="orderdate" data-rel="-">
						<label for="orderdate">DTU Month</label>				    
						<span class="form-control"><?php echo isset($_POST["dtu_month"])?$_POST["dtu_month"]:"";?></span>
						</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">Order Details</div>
		<div class="panel-body">
				<table class="table table-primary table-hover table-bordered">
				<thead><tr><td width="30%">Product</td><td width="30%">Unit Price</td><td width="20%">Qty</td><td width="20%">Amount</td></tr></thead>
				<tbody id="placeholder">
				<?php if(count($order_details)>0){?>
				<?php foreach($order_details as $key=>$value){?>
					<?php if(isset($cubicard_list[$currency])){ ?>
					<?php foreach($cubicard_list[$currency] as $key2=>$value2){?>
							<?php if(floatval($value2["value"])==floatval($value["value"])){?>
							<tr>
							<td><input class="hide" type="hidden" name="orderdata[desc][]" value="<?php echo $value2["desc"];?>"/><?php echo $value2["desc"]." | ".$value2["code"];?></td>
							<td><input class="hide" type="hidden" name="orderdata[price][]" value="<?php echo $value2["value"];?>"/><?php echo $value2["value"];?></td>
							<td><input class="hide" type="hidden" name="orderdata[qty][]" value="<?php echo $value["qty"];?>"/><?php echo $value["qty"];?></td>
							<td><span class="hide getTotal"><?php echo floatval($value2["value"])*floatval($value["qty"]);?></span><input class="hide" type="hidden" name="orderdata[product][]" value="<?php echo $value2["code"];?>"/><?php echo number_format(( floatval($value2["value"])*floatval($value["qty"]) ),"3",".","," );?></td>
							</tr>
							<?php } ?>
					<?php } ?>
					<?php } ?>
				<?php } ?>
				<tr>
				<td><input class="hide" type="hidden" name="orderdata[desc][]" value="Transaction Fee"/>Transaction Fee</td>
				<td><input id="transFeeValue" class="form-control" type="input" onkeyup="transFeeInput(this.value)" name="orderdata[price][]" value=""/></td>
				<td><input class="hide" type="hidden" name="orderdata[qty][]" value="1"/>1</td>
				<td><input type="hidden" name="orderdata[product][]" value="TRX FEE"/><span id="transFee"></span></td>
				</tr>
				<?php }else{ ?>
				<tr><td colspan="4" style="text-align:center"><strong>No Record(s) found!</strong></td></tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<tr><td colspan="3"><span class="pull-right">Total</span></td><td class="total_amount"></td></tr>
				<tr><td colspan="3"><span class="pull-right">Discount&nbsp;<input type="text" value="<?php echo isset($_POST["discount"])?$_POST["discount"]:0;?>" name="discount" size="3" onkeyup="calculator();"/> %</span></td><td id="cal_disc_amount"></td></tr>
				<tr><td colspan="3"><span class="pull-right">Grand Total</span></td><td id="grand_total_amount"></td></tr>
				<tr><td colspan="3"><span class="pull-right">Term</span></td><td><input type="text" value="<?php echo isset($_POST["term"])?$_POST["term"]:0;?>" name="term" size="4"/> Day(s)</td></tr>
				<tr><td colspan="3"><span class="pull-right">Tax Type</span></td><td><select name="tax"><option>Inclusive</option><option>Exclusive</option></select></td></tr>
				</tfoot>
				</table>
				<div align="center">
					<input id="btn_submit" class="btn btn-primary" <?php if(count($order_details)==0 || $currency==""){echo "disabled";}else{?>  onclick="document.getElementById('submitDTU').value='go';" <?php } ?> type="submit"/>
					<a href="index.php?r=pin/getpendingdtu" class="btn btn-default">Back</a>
					<input type="hidden" id="submitDTU" name="submitDTU" value=""/>
				</div>
		</div>
	</div>
	</form>
</div>
<div style="position:absolute;top:30%;left:45%;z-index:999;display:none" id="loaderframe">
<div class="loader"></div>
</div>
<style type="text/css">
.loader {
    border: 16px solid #f3f3f3; /* Light grey */
    border-top: 16px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 120px;
    height: 120px;
    animation: spin 2s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
<script type="text/javascript">
function transFeeInput(obj)
{
	if(obj==""){ obj = 0.00;}
	$("#transFee").html(parseFloat(obj).toLocaleString(undefined,{maximumSignificantDigits: 18}));
	calculator();
}

function loadingScreen()
{
	$("#loaderframe").css("display","");
}


function calculator()
{
	var ttl = 0;
	var discount_amount = 0;
	var disc = parseFloat($("input[name=discount]").val());
	var transFee = parseFloat($("#transFeeValue").val());
	var grand_total_amount = 0;
	$(".getTotal").each(function(index){
		ttl = ttl + parseFloat($(this).text());
	});
	$(".total_amount").html(ttl.toFixed(3));
	
	if(ttl>0)
	{
		//do discount
		if(disc>0)
		{
			discount_amount = ttl*(disc/100);
			$("#cal_disc_amount").text(discount_amount);
			grand_total_amount = (ttl-discount_amount);
		}else{
			$("#cal_disc_amount").text("0.00");
			grand_total_amount= ttl;
		}
		//minus transFee
		if(transFee>0)
		{
			$("#grand_total_amount").text((grand_total_amount-transFee).toFixed(3));
		}
		else
		{
			$("#grand_total_amount").text(grand_total_amount.toFixed(3));
		}
	}
}
window,onload = function(){calculator();}
window.onsubmit = function(){loadingScreen();}
</script>
<?php }else{ ?>
<div class="alert alert-info">
  <strong>Info!</strong> No details given for pending DTU, please try again.<br/>
  <a href="index.php?r=pin/getpendingdtu" class="btn btn-default">Back</a>
</div>
<?php } ?>