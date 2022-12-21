<h1>Generate Pin</h1>

<div class="row-fluid sortable">
    <div class="box span12">
 
	<div class="box-content">
		<div class="box-content">

				<fieldset>
					<div class="control-group" title="productID" data-rel="-">
					<label class="control-label" for="productID">Product ID</label>				    				    
					<div class="controls">
						<select name="productID" id="productID">
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
					</div>
					
					<div class="control-group" title="distroid" data-rel="-">
					<label class="control-label" for="distroid">Distributors</label>				    				    
					<div class="controls">
						<select name="distroid" id="distroid">
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
					</div>
					
					<div class="control-group" title="batchID" data-rel="-">
					<label class="control-label" for="batchID">BatchID</label>				    				    
					<div class="controls">
						<input type="text" name="batchID" placeholder="Batch ID"/>			
					</div>
					</div>
					
					
					<div class="control-group" title="invoice" data-rel="-">
					<label class="control-label" for="invoice">Invoice Number</label>				    				    
					<div class="controls">
						<input type="text" name="invoice" placeholder="Invoice Number"/>			
					</div>
					</div>
					
					<div class="control-group" title="issue_date" data-rel="-">
					<label class="control-label" for="issue_date">Issue Date</label>				    				    
					<div class="controls">
					<?php $this->widget('zii.widgets.jui.CJuiDatePicker',array(
					    'name'=>'issue_date',
					    'value'=>'',
					    'id'=>'issue_date',
					    // additional javascript options for the date picker plugin
					    'options'=>array(
						'showAnim'=>'fold',
					    ),
					    'htmlOptions'=>array(
						'style'=>'height:20px;'
					    ),
					)); ?>
					</div>
					</div>
					
					<div class="control-group" title="expire_date" data-rel="-">
					<label class="control-label" for="expire_date">Expire Date</label>				    				    
					<div class="controls">
					<?php $this->widget('zii.widgets.jui.CJuiDatePicker',array(
					    'name'=>'expire_date',
					    'value'=>'',
					    'id'=>'expire_date',
					    // additional javascript options for the date picker plugin
					    'options'=>array(
						'showAnim'=>'fold',
					    ),
					    'htmlOptions'=>array(
						'style'=>'height:20px;'
					    ),
					)); ?>
					</div>
					</div>
					
					<div class="control-group" title="qty" data-rel="-">
					<label class="control-label" for="qty">Quantity</label>				    				    
					<div class="controls">
						<input type="text" name="qty" value="0" placeholder="Quantity" maxlenght="5"/>		
					</div>
					</div>
					<button class="btn btn-primary" onclick="callAjax()">Generate</button>
				</fieldset>
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
	$.post("index.php?r=cbz/AjaxGeneratePin",
		  {"productID":product,"distroid":distroid,"batchID":batchID,"invoice":invoice,"issue_date":issue_date,"expire_date":expire_date,"qty":qty},
		  function(data){},"json");
	}
	else
	{
		alert('Please fill in all the fields');
	}
}
</script>