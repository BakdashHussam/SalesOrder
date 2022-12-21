<?php if(isset($success) && $success===true){ ?>
	<div class="alert alert-success">
	  <strong>Action complete Successful!</strong>
	</div>	
<?php } ?>
<div class="row-fluid sortable">
	<div class="panel panel-primary">
		<div class="panel-heading">New Customer</div>
		<div class="panel-body">
					<form method="post">
						<div class="col-md-6 form-group" title="Cubit Rate Code" data-rel="-">
						<label for="rate_code">Rate Code</label>				    				    
							<select class="form-control" name="rate_code">
							<?php
							foreach ($game_code as $item) {
							  echo '<option value="' .$item["Name"] . '">' . $item["Name"] . '</option>';
							}
							?>
							</select>
						</div>	
						
						<div class="col-md-6 form-group" title="Cubit Rate Value" data-rel="-">
						<label for="rate_value">Cubit Rate Value</label>				    				    
							<input class="form-control" type="number" name="rate_value" step="any" placeholder="Enter Cubit Rate Value"/>
						</div>	
						
						<div class="col-md-6 form-group" title="cntype" data-rel="-">
						<label for="cntype">Cubit Rate Currency</label>
						<select class="form-control" name="rate_currency" id="rate_currency">
						<option value="MYR">Malaysia  Ringgit (MYR)</option>
						<option value="THB">Thai Baht (THB)</option>
						</select>
						</div>
						
						<div class="col-md-12" align="center">
							<input class="btn btn-primary" type="submit" value="Submit"/>
							<a href="index.php?r=pin/index" class="btn btn-default">Back</a>
							<input type="hidden" name="addNew" value="1"/>
						</div>
					</form>
		</div>
	</div>
</div>

<div class="row-fluid sortable">
	<div class="panel panel-primary">
		<div class="panel-heading">Manage Cubit Rate</div>
		<div class="panel-body">
				<table class="table table-primary table-hover table-responsive">
				<tr>
				<th>Cubit Rate Code</th>
				<th>Cubit Rate Value</th>
				<th>Cubit Rate Currency</th>
				<th>Last Update Date</th>
				<th>History</th>
				</tr>
				<?php foreach($rate_list as $key=>$value){ ?>
				<tr>
						<td><?php echo $value["rate_code"];?></td>
						<td>1 Cubits = <?php echo number_format($value["rate_value"],"10",".",",");?></td>
						<td><?php echo $value["rate_currency"];?></td>
						<td><?php echo date("d M Y h:i:s a",strtotime($value["latest_date"]));?></td>
						<td><span class="btn btn-primary btn-xs" onclick="getList('<?php echo $value["rate_code"];?>');">View</span></td>
				</tr>
				<?php } ?>
				</table>
				<div align="center">
					<a href="index.php?r=pin/index" class="btn btn-default">Back</a>
				</div>
		</div>
	</div>
</div>


<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Cubit Rate History</h4>
      </div>
      <div class="modal-body" id="history_holder">
		
      </div>
      <div class="modal-footer">
		<span class="btn btn-default" data-dismiss="modal">Close</span>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
function getList(id)
{
	$('#myModal').modal();
	$("#history_holder").html("");
	
	$.post("index.php?r=pin/cubitratehistory",{"id":id},function(data)
	{
		html = '<table class="table table-primary table-hover">';
		html += '<tr>';
		html += '<th>Cubit Rate Code</th>';
		html += '<th>Cubit Rate Value</th>';
		html += '<th>Cubit Rate Currency</th>';
		html += '<th>Last Update Date</th>';
		html += '<th>Last Update User</th>';
		html += '</tr>';
		
		
		for(var i in data)
		{
			current="";
			if(i==0){current = '<br><span class="label label-success">Current</span>';}
			html += "<tr>";
			html += "<td>"+data[i].rate_code+current+"</td>";
			html += "<td>"+ fn(data[i].rate_value)+"</td>";
			html += "<td>"+ data[i].rate_currency+"</td>";
			html += "<td>"+ data[i].create_date+"</td>";
			html += "<td>"+ data[i].create_user+"</td>";
			html += "</tr>";
		}
		html += '</table>';
		$("#history_holder").html(html);
	},"json");
}

function fn(number)
{
	return new Intl.NumberFormat('en-IN', { style: 'decimal', minimumFractionDigits : '10' }).format(number)
}
</script>