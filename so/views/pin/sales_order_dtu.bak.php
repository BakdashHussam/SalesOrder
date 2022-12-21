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
						<select class="form-control" name="customer" id="customer" onchange="getCustomer();">
							<option value="">Select Customer</option>
							<?php 
								if(isset($customerlist) && count($customerlist))
								{
									foreach($customerlist as $key=>$value)
									{
										$selected="";
										if(isset($_POST["customer"]) && $_POST["customer"]==$value["customer_code"]){ $selected = "selected";}
										echo "<option value='".$value["customer_code"]."' data-id='".$value["customer_id"]."' data-discount='".$value["discount"]."' data-currency='".$value["currency"]."' data-term='".$value["term"]."' ".$selected.">".$value["customer_name"]."</option>";
									}
								}
							?>
						</select>
						<input type="hidden" name="customer_id"/>
						<input type="hidden" name="customer_name"/>
						<input type="hidden" name="currency"/>
						</div>
						
						<div class="col-md-6 form-group" title="orderdate" data-rel="-">
						<label for="orderdate">Order Date</label>				    
							<?= yii\jui\DatePicker::widget(
							[
								'name' => 'orderdate',
								'value' => ((isset($_POST["orderdate"]) && $_POST["orderdate"]!="")?date('Y-m-d',strtotime($_POST["orderdate"])):date('Y-m-d')),
								'dateFormat'=>'yyyy-MM-dd',
								'options'=>[
										'class'=>'form-control',
										'placeholder'=>'Pick a date'
										]
							]
							) ?>
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
							<td><input class="hide" type="hidden" name="orderdata[product][]" value="<?php echo $value2["code"];?>"/><?php echo number_format(( floatval($value2["value"])*floatval($value["qty"]) ),"3",".","," );?></td>
							</tr>
							<?php } ?>
					<?php } ?>
					<?php } ?>
				<?php } ?>
				<tr>
				<td><input class="hide" type="hidden" name="orderdata[desc][]" value="Transaction Fee"/>Transaction Fee</td>
				<td><input class="form-control" type="input" name="orderdata[price][]" value="" onkeyup="document.getElementById('transFee').value=this.value"/></td>
				<td><input class="hide" type="hidden" name="orderdata[qty][]" value="1"/>1</td>
				<td><input type="hidden" name="orderdata[product][]" value="DISC"/><span id="transFee"></span></td>
				</tr>
				<?php }else{ ?>
				<tr><td colspan="4" style="text-align:center"><strong>No Record(s) found!</strong></td></tr>
				<?php } ?>
				</tbody>
				<tfoot>
				<tr><td colspan="3"><span class="pull-right">Discount(%)</span></td><td><input type="text" value="0" name="discount" size="4"/> %</td></tr>
				<tr><td colspan="3"><span class="pull-right">Term</span></td><td><input type="text" value="0" name="term" size="4"/> Day(s)</td></tr>
				<tr><td colspan="3"><span class="pull-right">Tax Type</span></td><td><select name="tax"><option>Exclusive</option><option>Inclusive</option></select></td></tr>
				</tfoot>
				</table>
				<div align="center">
					<input id="btn_submit" class="btn btn-primary" <?php if(count($order_details)==0 || $currency==""){echo "disabled";}else{?>  onclick="document.getElementById('submitDTU').value='go';" <?php } ?> type="submit"/>
					<a href="index.php?r=pin/listso" class="btn btn-default">Back</a>
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
function loadCustomer()
{
	if($("#customer option:selected").val()!="")
	{
		discount_rate = $("#customer option:selected").attr("data-discount");
		customer_id = $("#customer option:selected").attr("data-id");
		currency = $("#customer option:selected").attr("data-currency");
		customer_name = $("#customer option:selected").text();
		term = $("#customer option:selected").attr("data-term");
		if(discount_rate.length==0){discount_rate=0;} 
		$("input[name=discount]").val(discount_rate);
		$("input[name=term]").val(term);
		$("input[name=customer_id]").val(customer_id);
		$("input[name=customer_name]").val(customer_name);
		$("input[name=currency]").val(currency);
	}
}

function getCustomer()
{
	if($("#customer option:selected").val()!="")
	{
		loadCustomer();
		$("#btn_submit").attr("disabled","disabled");
		loadingScreen();
		document.getElementById("frm").submit();
	}
}

function loadingScreen()
{
	$("#loaderframe").css("display","");
}

window.onload = function(){
	loadCustomer();
};
</script>