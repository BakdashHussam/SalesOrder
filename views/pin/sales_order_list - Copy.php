<div class="row-fluid sortable">
    <div class="panel panel-primary">
		<div class="panel-heading">Sales Order List</div>
		<div class="panel-body">
		<?php if(isset($sales_order_list) && !empty($sales_order_list)){  ?>


	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">	
		google.charts.load('current', {'packages':['table']});
		google.charts.setOnLoadCallback(drawTable);
		var result = [<?php echo $sales_order_list;?>];
      function drawTable() {
		
		var data = new google.visualization.DataTable();
        data.addColumn('string', 'Sales Order Number');
		data.addColumn('string', 'Sales Order Date');
		data.addColumn('string', 'Customer');
		data.addColumn('string', 'Approval Status');
		data.addColumn('string', 'Action');
        data.addRows(result);

        var table = new google.visualization.Table(document.getElementById('table_div'));

		table.draw(data, {showRowNumber: true, width: '100%', height: '100%', page:'enable', pageSize:20, sortColumn:1});
		google.visualization.events.addListener(table, 'sort', loadURL);
		google.visualization.events.addListener(table, 'page', loadURL);
		loadURL();
      }
	  
		function loadURL()
		{
			var tables = $("table[class=google-visualization-table-table]").find("tbody").children();
			$.each( tables, function( key, value ) {
					id = $(value).children().eq(5).html();
					if(id.substr(0,7)=="<a href"){
						
					}else{
						view_url='<a title="Edit" class="btn btn-primary btn-xs" href="index.php?r=pin/viewso&id='+id+'"><i class="glyphicon glyphicon-search"></i></a>';
						edit_url='<a title="Edit" class="btn btn-primary btn-xs" href="index.php?r=pin/editso&id='+id+'"><i class="glyphicon glyphicon-edit"></i></a>';
						$(value).children().eq(5).html(view_url+"  "+edit_url);
					}
			});
		}
			</script>
			<div id="table_div">Loading...</div>
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
