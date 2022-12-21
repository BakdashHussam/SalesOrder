
<h1>Update Pin Status</h1>
<div class="row-fluid sortable">
<div class="panel panel-primary">
  <div class="panel-heading">List Pin</div>
  <div class="panel-body">
<form method="post" onsubmit="return checkMode();">
					<div class="form-group" title="batchID" data-rel="-">
					<label for="batchID">BatchID</label>				    				    
						<select class="form-control" name="batchID" id="batchID">
						<option value="">Please Select</option>
						<?php if(count($batchlist)>0)
								{
								foreach($batchlist as $key=>$value)
								{
									$selected="";
									if(isset($postdata["batchID"]) && $postdata["batchID"]==$value["batchid"]){ $selected="selected";}
									echo '<option value="'.$value["batchid"].'" '.$selected.'>'.$value["batchid"].'</option>';
								}
								}
						?>
						</select>
					</div>
					
					<div class="form-group" title="invoice" data-rel="-">
					<label for="invoice">Invoice Number</label>				    				    
						<input class="form-control" type="text" name="invoice_no" placeholder="Invoice Number" value="<?php echo (isset($postdata["invoice_no"]))?$postdata["invoice_no"]:"";?>"/>			
					</div>
					
					<div class="form-group" title="batchprefix" data-rel="-">
					<label for="batchprefix">Batch Prefix</label>				    				    
						<input class="form-control" type="text" name="batchprefix" placeholder="Example: ABC200707XXXXXX, the first 7 characters" maxlenght="7" value="<?php echo (isset($postdata["batchprefix"]))?$postdata["batchprefix"]:"";?>"/>
					</div>
					
					<div class="form-group" title="start sequence" data-rel="-">
					<label for="start_sequence">Start Squence</label>				    				    
						<input class="form-control" type="text" name="start" placeholder="Example: ABC200707XXXXXX, the 6 digit of the XXXXXX" maxlenght="6" value="<?php echo (isset($postdata["start"]))?$postdata["start"]:"";?>"/>		
					</div>
					
					<div class="form-group" title="end sequence" data-rel="-">
					<label for="end_sequence">End Squence</label>				    				    
						<input class="form-control" type="text" name="end" placeholder="Example: ABC200707XXXXXX, the 6 digit of the XXXXXX" maxlenght="6" value="<?php echo (isset($postdata["end"]))?$postdata["end"]:"";?>"/>		
					</div>
					
					<div class="form-group" title="status" data-rel="-">
					<label for="status">Status</label>				    				    
						<select class="form-control" name="status" id="status">
						<option value="">Please Select</option>
						<?php
							$statuslist = array("0"=>"Active","1"=>"Blocked","2"=>"Suspend");
							foreach($statuslist as $key=>$value)
							{
								$selected="";
								if(isset($postdata["status"]) && $postdata["status"]==$key){ $selected="selected";}
								echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
							}
						?>
						</select>				
					</div>
					<div class="btn btn-info">
					<span class="radio">
					<label for="listPin"><input type="radio" name="chkmode" id="listPin" value="listPin"/>List Pin before Update</label>
					</span>
					</div>
					<div class="btn btn-danger">
					<span class="radio">
					<label for="updatePinNow"><input type="radio" name="chkmode" id="updatePinNow" value="updatePinNow"/>Direct Update Pin</label>
					</span>
					</div>
					
					<div align="center">
					<input class="btn btn-primary" type="submit" name="get" onclick="this.value='loading...';$('input[name=get]').attr('disabled');" value="Submit"/>
					<button class="btn btn-default" onclick="window.history.back();">Back</button>
					</div>

</form>
	</div>
  </div>
</div>
<?php if(isset($result) && !empty($result)){ ?>
<div class="row-fluid sortable">
    <div class="box span12">
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">	
		google.charts.load('current', {'packages':['table']});
		google.charts.setOnLoadCallback(drawTable);
		var result = [<?php foreach($result as $key=>$value){
					echo '['.
						'"'.$value["cardserial"].'",'.
						'"'.$value["cardcode"].'",'.
						'"'.$value["batchid"].'",'.
						'"'.$value["distroid"].'",'.
						'"'.$value["ownerid"].'",'.
						//'"'.$value["expire_date"].'",'.
						//'"'.$value["issued_date"].'",'.
						'new Date('.date("Y",strtotime($value["expire_date"])).', '.(date("m",strtotime($value["expire_date"]))-1).', '.date("d",strtotime($value["expire_date"])).', '.date("h",strtotime($value["expire_date"])).', '.date("i",strtotime($value["expire_date"])).','.date("s",strtotime($value["expire_date"])).'),'.
						'new Date('.date("Y",strtotime($value["issued_date"])).', '.(date("m",strtotime($value["issued_date"]))-1).', '.date("d",strtotime($value["issued_date"])).', '.date("h",strtotime($value["issued_date"])).', '.date("i",strtotime($value["issued_date"])).','.date("s",strtotime($value["issued_date"])).'),'.
						(($value["blocked"]==1)?"true":"false").
					'],';
					}?>];
      function drawTable() {
		
		var data = new google.visualization.DataTable();
        data.addColumn('string', 'Cardserial');
		data.addColumn('string', 'Cardcode');
		data.addColumn('string', 'BatchID');
		data.addColumn('string', 'Distributor');
		data.addColumn('string', 'CardUsed UserID');
		data.addColumn('date', 'Expire Date');
		data.addColumn('date', 'Issued Date');
        data.addColumn('boolean', 'Blocked');
        data.addRows(result);

        var table = new google.visualization.Table(document.getElementById('table_div'));

        table.draw(data, {showRowNumber: true, width: '100%', height: '100%', page:'enable', pageSize:50});
      }
</script>
	<div id="table_div">Loading...</div>
	</div>
</div>
<?php }else if(isset($result) && empty($result)){ ?>
<h4>No Record(s)!</h4>
<?php } ?>
<script type="text/javascript">
function checkMode()
{
	var go = false;
	if($('input[name="chkmode"]:checked').length > 0)
	{
		go = true;
	}
	else
	{
		$('input[name="chkmode"]').parent().css("border","1px solid red");
		alert("Please select the options");
	}
	return go;
}
</script>
<style type="text/css">
.forms input,select
{
	width:100%;
}
</style>