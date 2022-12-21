<?php if(isset($success) && $success===true){ ?>
	<div class="alert alert-success">
	  <strong>Successfully Added!</strong>
	</div>	
<?php } ?>
<div class="row-fluid sortable">
	<form method="post">
	<div class="panel panel-primary">
		<div class="panel-heading">Create User</div>
		<div class="panel-body">

						<div class="col-md-6 form-group" title="Username" data-rel="-">
						<label for="Username">Username</label>				    				    
							<input class="form-control" type="text" name="username" placeholder="Enter Username"/>
						</div>			

						<div class="col-md-6 form-group" title="Password" data-rel="-">
						<label for="Password">Password</label>				    				    
							<input class="form-control" type="Password" name="password" placeholder="Enter Password"/>
						</div>	
						
						<div class="col-md-6 form-group" title="Email" data-rel="-">
						<label for="Email">Email</label>				    				    
							<input class="form-control" type="Email" name="email" placeholder="Enter Email"/>
						</div>	
					
						<div class="col-md-6 form-group" title="Role" data-rel="-">
						<label for="Role">Role</label>				    				    
							<select class="form-control" name="role">
							<?php if(Yii::$app->user->identity->role=="admin"){?>
								<option value="admin">Admin</option>
							<?php } ?>
							<?php if(Yii::$app->user->identity->role!="staff"){?>
								<option value="finance">Finance</option>
							<?php } ?>
								<option value="staff">Staff</option>
							</select>
						</div>
						
						<div class="col-md-6 form-group" title="Status" data-rel="-">
						<label for="Status">Status</label>		
							<select class="form-control" name="status">
								<option value="1">Active</option>
								<option value="0">Inactive</option>
							</select>		
						</div>
						
						<div class="col-md-12" align="center">
							<input class="btn btn-primary" type="submit" value="Submit"/>
							<a class="btn btn-default" href="index.php?r=pin/index">Back</a>
						</div>
		</div>
	</div>
</form>
</div>