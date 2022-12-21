<div class="row-fluid sortable">
<div class="panel panel-primary">
  <div class="panel-heading">Generate Pin</div>
  <div class="panel-body">

					<div class="form-group" title="productID" data-rel="-">
					<label for="productID">Product ID</label>				    				    
					<select class="form-control" name="productID" id="productID">
						<option value="">Please Select</option>
					<?php if(count($product)>0)
							{
							foreach($product as $key=>$value)
							{
								echo '<option value="'.$value["productid"].'">'.$value["productid"].' | '.$value["currency"].' '.number_format($value["value_real"],'2','.','').' ('.number_format($value["value_cubits"],'2','.','').' cubits)</option>';
							}
							}
					?>
					</select>				
					</div>

					<div class="form-group" title="distroid" data-rel="-">
					<label for="distroid">Distributors</label>				    				    
					<select class="form-control" name="distroid" id="distroid">
						<option value="">Please Select</option>
						<?php if(count($distroid)>0)
								{
								foreach($distroid as $key=>$value)
								{
									echo '<option value="'.$value["distroid"].'">'.$value["distroname"].'</option>';
								}
								}
						?>
					</select>
					</div>
					<div class="form-group" title="issue_date" data-rel="-">
					<label for="issue_date">Issue Date</label>				    
						<?= yii\jui\DatePicker::widget(
						[
							'name' => 'issue_date',
							'dateFormat'=>'yyyy-MM-dd',
							'options'=>[
									'class'=>'form-control',
									'placeholder'=>'Pick a date'
									]
						]
						) ?>
					</div>

					<div class="form-group" title="expire_date" data-rel="-">
					<label for="expire_date">Expire Date</label>				    				    
						<?= yii\jui\DatePicker::widget(
						[
							'name' => 'expire_date',
							'dateFormat'=>'yyyy-MM-dd',
							'options'=>[
									'class'=>'form-control',
									'placeholder'=>'Pick a date'
									]
						]
						) ?>
					</div>
					
					<div class="form-group" title="batchID" data-rel="-">
					<label for="batchID">BatchID</label>				    				    
						<input class="form-control" type="text" name="batchID" placeholder="Batch ID"/>			
					</div>
					
					<div class="form-group" title="invoice" data-rel="-">
					<label for="invoice">Invoice Number</label>				    				    
						<input class="form-control" type="text" name="invoice" placeholder="Invoice Number"/>			
					</div>
					
					<div class="form-group" title="qty" data-rel="-">
					<label for="qty">Quantity</label>				    				    
						<input class="form-control" type="text" name="qty" value="0" placeholder="Quantity" maxlenght="5"/>		
					</div>
					<div align="center">
					<button class="btn btn-primary" onclick="callAjax()">Generate</button>
					<button class="btn btn-default" onclick="window.history.back();">Back</button>
					</div>
  </div>
</div>
</div>
<script type="text/javascript">
function callAjax()
{
	go = true;
	product = $("select[name=productID] option:selected").val();
	distroid = $("select[name=distroid] option:selected").val();
	batchID = $("input[name=batchID]").val();
	invoice = $("input[name=invoice]").val();
	issue_date = $("input[name=issue_date]").val();
	expire_date = $("input[name=expire_date]").val();
	qty = $("input[name=qty]").val();
	
	if(product==""){ go = false;}
	if(distroid==""){ go = false;}
	if(batchID==""){ go = false;}
	if(invoice==""){ go = false;}
	if(issue_date==""){ go = false;}
	if(expire_date==""){ go = false;}
	if(isNaN(parseInt(qty)) && parseInt(qty)==0){ go = false;}
	
	if(go==true)
	{
	$.post("index.php?r=pin/Generatepin",
		  {"productID":product,"distroid":distroid,"batchID":batchID,"invoice":invoice,"issue_date":issue_date,"expire_date":expire_date,"qty":qty},
		  function(data){},"json");
	}
	else
	{
		alert('Please fill in all the fields');
	}
}
</script>