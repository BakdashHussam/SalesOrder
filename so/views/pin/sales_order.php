<div class="row-fluid sortable">
	<form method="post">
	<div class="panel panel-primary">
		<div class="panel-heading">Create Sales Order</div>
		<div class="panel-body">

						<div class="col-md-6 form-group" title="saleorderno" data-rel="-">
						<label for="saleorderno">Sales Order Number</label>				    				    
							<input class="form-control" disabled type="text" name="saleorderno" value="SYSTEM GENERATE"/>
						</div>			

						<div class="col-md-6 form-group" title="customer" data-rel="-">
						<label for="customer">Customer</label>				    				    
						<select class="form-control" name="customer" id="customer" onchange="getCustomer();">
							<?php 
								if(isset($customerlist) && count($customerlist))
								{
									foreach($customerlist as $key=>$value)
									{
										echo "<option value='".$value["customer_code"]."' data-id='".$value["customer_id"]."' data-discount='".$value["discount"]."' data-currency='".$value["currency"]."' data-term='".$value["term"]."'>".$value["customer_name"]."</option>";
									}
								}
							?>
						</select>
						<input type="hidden" name="customer_id"/>
						<input type="hidden" name="customer_name"/>
						</div>
						
						<div class="col-md-6 form-group" title="orderdate" data-rel="-">
						<label for="orderdate">Order Date</label>				    
							<?= yii\jui\DatePicker::widget(
							[
								'name' => 'orderdate',
								'value' => date('Y-m-d'),
								'dateFormat'=>'yyyy-MM-dd',
								'options'=>[
										'class'=>'form-control',
										'placeholder'=>'Pick a date'
										]
							]
							) ?>
						</div>
					
						<!--div class="col-md-6 form-group" title="invoice" data-rel="-">
						<label for="invoice">Invoice Number</label>				    				    
							<input class="form-control" type="text" name="invoice_no" placeholder="Invoice Number"/>			
						</div-->
						
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">Order Details</div>
		<div class="panel-body">
				<div class="col-md-5 form-group" title="productID" data-rel="-">
				<label for="productID">Product</label>				    				    
				<select class="form-control" name="productID" id="productID">
					<option value="">Please Select</option>
					<?php 
						if(isset($productlist) && count($productlist)>0)
						{
							foreach($productlist as $key=>$value)
							{
								echo "<option data-value='".$value["product_value"]."' data-desc='".$value["product_desc"]."' data-tax='".$value["product_tax"]."' value='".$value["product_code"]."'>".$value["product_code"]." - ".$value["product_desc"]."</option>";
							}
						}
					?>
				</select>
				</div>
				
				<div class="col-md-5 form-group" title="qty" data-rel="-">
				<label for="qty">Quantity</label>				    				    
					<input class="form-control" type="text" id="qty" name="qty" value="0" placeholder="Quantity" maxlenght="5"/>		
				</div>
				
				<div class="col-md-2 form-group">
					<span id="btn_add" class="btn btn-default form-control" style="margin-top:25px" onclick="add()"><i class="glyphicon glyphicon-plus"></i> Add</span>
					<span id="btn_loading" class="btn btn-default form-control disabled" style="margin-top:25px;display:none;">Loading....</span>
				</div>
				
				<table class="table table-primary table-hover table-bordered">
				<thead><tr><td width="5%">Action</td><td width="25%">Product</td><td width="10%">Unit Price</td><td width="10%">Qty</td><td width="15%">Amount</td></tr></thead>
				<tbody id="placeholder"></tbody>
				<tfoot>
				<tr><td colspan="4"><span class="pull-right">Discount&nbsp;<input type="hidden" value="0" name="discount_ori"/><input type="text" value="0" name="discount" size="3" onkeyup="calculator()"/> %</span></td><td id="cal_disc_amount"></td></tr>
				<tr><td colspan="4"><span class="pull-right">Sub Total</span></td><td class="total_amount"></td></tr>
				<tr><td colspan="4"><span class="pull-right">Total Tax</span></td><td class="total_tax_amount"></td></tr>
				<tr><td colspan="4"><span class="pull-right">Grand Total</span></td><td id="grand_total_amount"></td></tr>
				<tr><td colspan="4"><span class="pull-right">Term</span></td><td><input type="text" value="0" name="term" size="4"/> Day(s)</td></tr>
				<tr><td colspan="4"><span class="pull-right">Tax Type</span></td><td><select name="tax" onchange="calculator();"><option value="Inclusive">Inclusive</option><option value="Exclusive">Exclusive</option></select></td></tr>
				</tfoot>
				</table>
				<div align="center">
					<input id="btn_submit" class="btn btn-primary" disabled type="submit"/>
					<input id="tax_rate" name="tax_rate" type="hidden" value="0"/>
					<a href="index.php?r=pin/listso" class="btn btn-default">Back</a>
				</div>
		</div>
	</div>
	</form>
</div>
<script type="text/javascript">
function getCustomer()
{
	discount_rate = $("#customer option:selected").attr("data-discount");
	customer_id = $("#customer option:selected").attr("data-id");
	customer_name = $("#customer option:selected").text();
	term = $("#customer option:selected").attr("data-term");
	if(discount_rate.length==0){discount_rate=0;} 
	$("input[name=discount]").val(discount_rate);
	$("input[name=discount_ori]").val(discount_rate);
	$("input[name=term]").val(term);
	$("input[name=customer_id]").val(customer_id);
	$("input[name=customer_name]").val(customer_name);
	calculator();
}

function add()
{
	$(".btn_add").hide();
	$(".btn_loading").show();
	product = $("#productID option:selected").val();
	product_price = $("#productID option:selected").attr("data-value");
	productText = $("#productID option:selected").attr("data-desc");
	batch = $("#batchID option:selected").val();
	batchText = $("#batchID option:selected").text();
	qty = parseInt($("#qty").val());
	taxtype = $("#productID option:selected").attr("data-tax");
	
	tax = $("#tax_rate").val();
	
	if(tax==0)
	{
		$.post("index.php?r=pin/taxrate",{taxtype:taxtype},function(data){
			tax = data;
			$("#tax_rate").val(tax);
			if(product!="" && batch!="" && qty>0)
			{
				getTotal = qty* product_price;
				theAmount = getTotal;
				html = '<tr>'
						+'<td><i class="glyphicon glyphicon-trash" onclick="remove(this)" style="cursor:pointer"></i></td>'
						+'<td><input class="hide" type="hidden" name="orderdata[desc][]" value="'+productText+'"/><input class="hide" type="hidden" name="orderdata[product][]" value="'+product+'"/>'+productText+'</td>'		
						+'<td><input class="hide" type="hidden" name="orderdata[price][]" value="'+product_price+'"/>'+product_price+'</td>'
						+'<td><input class="hide" type="hidden" name="orderdata[qty][]" value="'+qty+'"/><input class="hide" type="hidden" name="orderdata[tax][]" value="'+tax+'"/>'+qty+'</td>'
						+'<td>'+theAmount+'</td>'
						+'</tr>';
				$("#placeholder").append(html);
				$("#btn_submit").attr("disabled",false);
				calculator();
			}
			else
			{
				alert("Please provide proper details!");
			}
		});
	}
	else
	{
		if(product!="" && batch!="" && qty>0)
		{
			getTotal = qty* product_price;
			theAmount = getTotal;
			html = '<tr>'
					+'<td><i class="glyphicon glyphicon-trash" onclick="remove(this)" style="cursor:pointer"></i></td>'
					+'<td><input class="hide" type="hidden" name="orderdata[desc][]" value="'+productText+'"/><input class="hide" type="hidden" name="orderdata[product][]" value="'+product+'"/>'+productText+'</td>'		
					+'<td><input class="hide" type="hidden" name="orderdata[price][]" value="'+product_price+'"/>'+product_price+'</td>'
					+'<td><input class="hide" type="hidden" name="orderdata[qty][]" value="'+qty+'"/><input class="hide" type="hidden" name="orderdata[tax][]" value="'+tax+'"/>'+qty+'</td>'
					+'<td>'+theAmount+'</td>'
					+'</tr>';
			$("#placeholder").append(html);
			$("#btn_submit").attr("disabled",false);
			calculator();
		}
		else
		{
			alert("Please provide proper details!");
		}
	}
	
}

function remove(obj)
{
	if(confirm("Remove this?")){
		$(obj).parent().parent().remove();
		if($("#placeholder").html()==""){
			$("#btn_submit").attr("disabled",true);
		}
		calculator();
	}
	
}


window.onload = function(){
	getCustomer();
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