<div class="row-fluid sortable">

	<div class="panel panel-default">
		<div class="panel-heading">Order Details</div>
		<div class="panel-body">
				
				
				<table class="table table-primary table-hover table-bordered">
				<thead><tr><td>date</td><td>50</td><td >90</td><td >150</td><td >300</td><td >500</td><td >1000</td></tr></thead>
				<tbody id="placeholder">
				<?php foreach($data as $key=>$value){ ?>
				<tr>
				<td><?php echo $key;?></td>
				<td class="cubit">50</td>
				<td><?php echo isset($value["50.0000"])?$value["50.0000"][0]:0;?></td>
				<td class="cubit">90</td>
				<td><?php echo isset($value["90.0000"])?$value["90.0000"]:0;?></td>
				<td class="cubit">150</td>
				<td><?php echo isset($value["150.0000"])?$value["150.0000"]:0;?></td>
				<td class="cubit">300</td>
				<td><?php echo isset($value["300.0000"])?$value["300.0000"]:0;?></td>
				<td class="cubit">500</td>
				<td><?php echo isset($value["500.0000"])?$value["500.0000"]:0;?></td>
				<td class="cubit">1000</td>
				<td><?php echo isset($value["1000.0000"])?$value["1000.0000"]:0;?></td>
				</tr>
				<?php } ?>
				</tbody>
				</table>
				
		</div>
	</div>
</div>

<button onclick="$('.cubit').hide();">abc</button>