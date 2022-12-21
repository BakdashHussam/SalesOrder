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
						<div class="col-md-6 form-group" title="Customer ID" data-rel="-">
						<label for="customer_id">Customer ID</label>
							<input class="form-control" type="number" name="customer_id" placeholder="Enter Customer ID"/>
						</div>			

						<div class="col-md-6 form-group" title="Customer Name" data-rel="-">
						<label for="customer_name">Customer Name</label>				    				    
							<input class="form-control" type="text" name="customer_name" placeholder="Enter Customer Name"/>
						</div>	
						
						<div class="col-md-6 form-group" title="Customer Code" data-rel="-">
						<label for="customer_code">Customer Code</label>				    				    
							<input class="form-control" type="text" maxlength="4" name="customer_code" placeholder="Enter Customer Code"/>
						</div>	
						
						<div class="col-md-6 form-group" title="Customer Status" data-rel="-">
						<label for="customer_flag">Customer Status</label>		
							<select class="form-control" name="customer_flag">
								<option value="1">Active</option>
								<option value="0">Inactive</option>
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
		<div class="panel-heading">Manage Customer</div>
		<div class="panel-body">
				<table class="table table-primary table-hover table-responsive">
				<tr>
				<th>Customer ID</th>
				<th>Customer Name</th>
				<th>Customer Code</th>
				<th>Active?</th>
				<th>Action</th>
				</tr>
				<?php foreach($customer_list as $key=>$value){ ?>
				<tr>
					<form method="post">
						<td><?php echo $value["customer_id"];?></td>
						<td><?php echo $value["customer_name"];?></td>
						<td><?php echo $value["customer_code"];?></td>
						<td>
						<input type="checkbox" id="customer_flag" name="customer_flag" value="1" <?php echo ($value["customer_flag"]==1)?"checked":"";?>/>
						</td>
						<td>
							<input class="btn btn-primary" type="submit" onclick="return confirm('Are you confirm to proceed with this action?');" value="Submit"/>
							<input type="hidden" name="update" value="1"/>
							<input type="hidden" name="customer_id" value="<?php echo $value["customer_id"];?>"/>
						</td>
					</form>
				</tr>
				<?php } ?>
				</table>
				<div align="center">
					<a href="index.php?r=pin/index" class="btn btn-default">Back</a>
				</div>
		</div>
	</div>
</div>