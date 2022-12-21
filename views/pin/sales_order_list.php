<div class="row-fluid sortable">
    <div class="panel panel-primary">
		<div class="panel-heading">Sales Order List</div>
		<div class="panel-body">
		<div class="row">
		<div class="col-md-2"><div id="string_filter_div"></div></div>
		<div class="col-md-2"><div id="string_filter_div2"></div></div>
		<div class="col-md-2"><div id="string_filter_div3"></div></div>
		</div>
		<?php if(isset($sales_order_list) && !empty($sales_order_list)){  ?>


	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">	
		google.charts.load('current', {'packages':['table']});
		google.charts.load('visualization', {'packages':['controls']});
		google.charts.setOnLoadCallback(drawTable);
		var result = [<?php echo $sales_order_list;?>];
					
      function drawTable() 
	  {
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Sales Order Number');
		data.addColumn('string', 'Sales Order Type');
		data.addColumn('date', 'Sales Order Date');
		data.addColumn('string', 'Customer');
		data.addColumn('string', 'Approval Status');
		data.addColumn('string', 'Invoice Number');
		data.addColumn('number', 'Amount');
		data.addColumn('number', 'Cubits');
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
		
		var stringFilter2 = new google.visualization.ControlWrapper({
			controlType: 'StringFilter',
			containerId: 'string_filter_div2',
			options: {
				filterColumnIndex: 3
			}
		});
		
		
		var stringFilter3 = new google.visualization.ControlWrapper({
			controlType: 'StringFilter',
			containerId: 'string_filter_div3',
			options: {
				filterColumnIndex: 5
			}
		});
		
		var table = new google.visualization.ChartWrapper({chartType: 'Table',containerId: 'table_div',
			options: {
				showRowNumber: true, width: '100%', height: '100%', page:'enable', pageSize:20, sortColumn:1
			}
		});
		
		
		
		var readyListener = google.visualization.events.addListener(table, 'ready',
		function() {
		  google.visualization.events.removeListener(readyListener)
		  google.visualization.events.addListener(table.getChart(), 'sort', loadURL); 
		  google.visualization.events.addListener(table.getChart(), 'page', loadURL);
		});
		
		loadURL();
		google.visualization.events.addListener(dashboard, 'ready', loadURL);
		
		
		dashboard.bind([stringFilter,stringFilter2,stringFilter3], [table]);
		dashboard.draw(data);
		
		
      }
	  
	  function loadURL()
		{
			var tables = $("table[class=google-visualization-table-table]").find("tbody").children();
			$.each( tables, function( key, value ) {
					id = $(value).children().eq(9).html();
					if(id.substr(0,7)=="<a href"){
						
					}else{
						view_url='<a title="View" class="btn btn-primary btn-xs" href="index.php?r=pin/viewso&id='+id+'"><i class="glyphicon glyphicon-search"></i></a>';
						$(value).children().eq(9).html(view_url);
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
					<a href="index.php?r=pin/createso" class="btn btn-primary">Create New</a>
					<a href="index.php?r=pin/index" class="btn btn-default">Back</a>
				</div>
	</div>
</div>
