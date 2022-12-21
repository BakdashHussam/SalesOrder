<div class="row-fluid sortable">
    <div class="panel panel-primary">
		<div class="panel-heading">Credit Note List</div>
		<div class="panel-body">
		<div id="string_filter_div"></div>
		<?php if(isset($credit_note_list) && !empty($credit_note_list)){  ?>


	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">	
		google.charts.load('current', {'packages':['table']});
		google.charts.load('visualization', {'packages':['controls']});
		google.charts.setOnLoadCallback(drawTable);
		var result = [<?php echo $credit_note_list;?>];
					
      function drawTable() 
	  {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Credit Note Number');
		data.addColumn('date', 'Credit Note Date');
		data.addColumn('number', 'Amount');
		data.addColumn('string', 'Approval Status');
		data.addColumn('string', 'Action');
        data.addRows(result);
		
		var dashboard = new google.visualization.Dashboard(document.querySelector('#dashboard'));
    
		var stringFilter = new google.visualization.ControlWrapper({
			controlType: 'StringFilter',
			containerId: 'string_filter_div',
			options: {
				filterColumnIndex: 0
			}
		});
		
		var table = new google.visualization.ChartWrapper({chartType: 'Table',containerId: 'table_div',
			options: {
				showRowNumber: true, width: '100%', height: '100%', page:'enable', pageSize:20, sortColumn:1
			}
		});
		var formatter = new google.visualization.NumberFormat(
				{ negativeColor: 'red', negativeParens: true});
			formatter.format(data, 2); // Apply formatter to thrid column
		
		
		
		var readyListener = google.visualization.events.addListener(table, 'ready',
		function() {
		  google.visualization.events.removeListener(readyListener)
		  google.visualization.events.addListener(table.getChart(), 'sort', loadURL); 
		  google.visualization.events.addListener(table.getChart(), 'page', loadURL);
		});
		
		loadURL();
		google.visualization.events.addListener(dashboard, 'ready', loadURL);
		
		
		dashboard.bind([stringFilter], [table]);
		dashboard.draw(data);
		
		
      }
	  
	  function loadURL()
		{
			var tables = $("table[class=google-visualization-table-table]").find("tbody").children();
			$.each( tables, function( key, value ) {
					id = $(value).children().eq(5).html();
					if(id.substr(0,7)=="<a href"){
						
					}else{
						view_url='<a title="View" class="btn btn-primary btn-xs" href="index.php?r=pin/viewcn&id='+id+'"><i class="glyphicon glyphicon-search"></i></a>';
						$(value).children().eq(5).html(view_url);
					}
			});
		}
			</script>
			<div id="dashboard">
			<div id="table_div">Loading...</div>
			</div>
		</div>
		<?php }else{ ?>
		<h4>No Record(s)!</h4>
		<?php } ?>
		
	</div>
	<div class="col-md-12">
				<div align="center">
					<a href="javascript:createCN()" class="btn btn-primary">Create New</a>
					<a href="index.php?r=pin/index" class="btn btn-default">Back</a>
				</div>
	</div>
</div>


<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
	<form action="index.php?r=pin/getsoforcn" method="post">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Add Credit Note</h4>
      </div>
      <div class="modal-body">
		
		<div class="form-group">
        <input class="form-control" type="text" name="sale_order_no" placeholder="Enter Sales Order Number here if available"/>
		</div>
		<div class="form-group">
		<input class="form-control" type="text" name="invoice_no" placeholder="Enter Invoice Number here if available"/>
		</div>
      </div>
      <div class="modal-footer">
        <input type="submit" class="btn btn-primary" value="Add"/>
		<span class="btn btn-default" data-dismiss="modal">Close</span>
      </div>
    </div>
	</form>
  </div>
</div>
<script type="text/javascript">
function createCN()
{
	$('#myModal').modal();
}

</script>