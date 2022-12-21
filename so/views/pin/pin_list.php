
<div class="row-fluid sortable">
<div class="panel panel-primary">
  <div class="panel-heading">Get Pin Details</div>
  <div class="panel-body">
			<form method="post">
				<div class="form-group" title="cardserial" data-rel="-">
				<label for="cardserial">Cardserial</label>				    				    
					<input class="form-control" type="text" name="cardserial" placeholder="Cardserial"/>			
				</div>
				
				<div class="form-group" title="cardcode" data-rel="-">
				<label for="cardcode">Cardcode</label>				    				    
					<input class="form-control" type="text" name="cardcode" placeholder="Cardcode"/>			
				</div>
				
				<div class="form-group" title="distroid" data-rel="-">
				<label for="distroid">Distributors</label>				    				    
				<select class="form-control" name="distroid" id="distroid">
					<option value="">Please Select</option>
					<?php if(count($distrolist)>0)
							{
								foreach($distrolist as $key=>$value)
								{
									echo '<option value="'.$value["distroid"].'">'.$value["distroname"].'</option>';
								}
							}
					?>
				</select>
				</div>
				
				<div class="form-group" title="batchid" data-rel="-">
				<label for="batchid">BatchID</label>				    				    
					<input class="form-control" type="text" name="batchid" placeholder="Batch ID"/>			
				</div>
				
				<div class="form-group" title="invoice_no" data-rel="-">
				<label for="invoice_no">Invoice Number</label>				    				    
					<input class="form-control" type="text" name="invoice_no" placeholder="Invoice Number"/>
				</div>
				<h5>Maximum show first 50,000 records</h5>
			
				<div align="center">
				<input class="btn btn-primary" type="submit" name="get" onclick="this.value='loading...';$('input[name=get]').attr('disabled');" value="Submit"/>
				<button class="btn btn-default" onclick="window.history.back();">Back</button>
				</div>
			</form>
	  </div>
	</div>			
</div>


<?php if(isset($result) && !empty($result)){  ?>

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
						'"'.$value["invoice_no"].'",'.
						'"'.$value["issued_by"].'",'.
						'new Date('.date("Y",strtotime($value["expire_date"])).', '.date("m",strtotime($value["expire_date"])).', '.date("d",strtotime($value["expire_date"])).', '.date("h",strtotime($value["expire_date"])).', '.date("i",strtotime($value["expire_date"])).','.date("s",strtotime($value["expire_date"])).'),'.
						'new Date('.date("Y",strtotime($value["issued_date"])).', '.date("m",strtotime($value["issued_date"])).', '.date("d",strtotime($value["issued_date"])).', '.date("h",strtotime($value["issued_date"])).', '.date("i",strtotime($value["issued_date"])).','.date("s",strtotime($value["issued_date"])).'),'.
						(($value["blocked"]==1)?"true":"false").
					'],';
					}?>];
      function drawTable() {
		
		var data = new google.visualization.DataTable();
        data.addColumn('string', 'Cardserial');
		data.addColumn('string', 'Cardcode');
		data.addColumn('string', 'BatchID');
		data.addColumn('string', 'Distributor');
		data.addColumn('string', 'Invoice No');
		data.addColumn('string', 'Issued By');
		data.addColumn('date', 'Issued Date');
		data.addColumn('date', 'Expire Date');
		data.addColumn('boolean', 'Blocked');
        data.addRows(result);

        var table = new google.visualization.Table(document.getElementById('table_div'));

        table.draw(data, {showRowNumber: true, width: '100%', height: '100%', page:'enable', pageSize:50});
      }
	  
$(document).ready(function(){
    $('.genExcel').click(function(){
		header = ['Cardserial','Cardcode','BatchID','Distributor','Invoice No', 'Issued by','Issued Date','Expire Date','Blocked'];
        result.unshift(header);
		var data = result;
        if(data == '')
            return;
        
        JSONToCSVConvertor(data, "Report", false);
    });
});

function JSONToCSVConvertor(JSONData, ReportTitle, ShowLabel) {
    //If JSONData is not an object then JSON.parse will parse the JSON string in an Object
    var arrData = typeof JSONData != 'object' ? JSON.parse(JSONData) : JSONData;
    
    var CSV = '';    
    //Set Report title in first row or line
    
    CSV += ReportTitle + '\r\n\n';

    //This condition will generate the Label/Header
    if (ShowLabel) {
        var row = "";
        
		
        //This loop will extract the label from 1st index of on array
        for (var index in arrData[0]) {
            
            //Now convert each value to string and comma-seprated
            row += index + ',';
        }

        row = row.slice(0, -1);
        
        //append Label row with line break
        CSV += row + '\r\n';
		
    }
    
    //1st loop is to extract each row
    for (var i = 0; i < arrData.length; i++) {
        var row = "";
        
        //2nd loop will extract each column and convert it in string comma-seprated
        for (var index in arrData[i]) {
            row += '"' + arrData[i][index] + '",';
        }

        row.slice(0, row.length - 1);
        
        //add a line break after each row
        CSV += row + '\r\n';
    }

    if (CSV == '') {        
        alert("Invalid data");
        return;
    }   
    
    //Generate a file name
    var fileName = "Cubicard_";
    //this will remove the blank-spaces from the title and replace it with an underscore
    fileName += ReportTitle.replace(/ /g,"_");   
    
    //Initialize file format you want csv or xls
    var uri = 'data:text/csv;charset=utf-8,' + escape(CSV);
    
    // Now the little tricky part.
    // you can use either>> window.open(uri);
    // but this will not work in some browsers
    // or you will not get the correct file extension    
    
    //this trick will generate a temp <a /> tag
    var link = document.createElement("a");    
    link.href = uri;
    
    //set the visibility hidden so it will not effect on your web-layout
    link.style = "visibility:hidden";
    link.download = fileName + ".xls";
    
    //this part will append the anchor tag and remove it after automatic click
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
    </script>
	<span class="genExcel btn btn-primary" onclick="downloadFile()">Export</span>
	<br/><br/>
	<div id="table_div">Loading...</div>
	<br/>
	<span class="genExcel btn btn-primary" onclick="downloadFile()">Export</span>
	</div>
</div>
<?php }else{ ?>
<h4>No Record(s)!</h4>
<?php } ?>