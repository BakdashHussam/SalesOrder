<?php if(isset($success) && $success===true){ ?>
	<div class="alert alert-success">
	  <strong>Successfully Changed!</strong>
	</div>	
<?php } ?>
<div class="row-fluid sortable">
	<form method="post">
	<div class="panel panel-primary">
		<div class="panel-heading">Change Password</div>
		<div class="panel-body">		

						<div class="col-md-12 form-group" title="Password" data-rel="-">
						<label for="Password">Password</label>				    				    
							<input class="form-control" type="Password" name="password" placeholder="Enter Password" onblur="pass1()"/>
						</div>	
						
						<div class="col-md-12 form-group" title="Password" data-rel="-">
						<label for="Password">Confirm Password</label>				    				    
							<input class="form-control" type="Password" name="password2" placeholder="Enter Password" onblur="pass1()"/>
							<span style="color:red;display:none" id="notmatch">Confirm password not match with password.</span>
						</div>
						
						<div class="col-md-12" align="center">
							<input class="btn btn-primary" type="submit" value="Submit"/>			
							<a class="btn btn-default" href="index.php?r=pin/index">Back</a>
						</div>
		</div>
	</div>
</form>
</div>
<script type="text/javascript">
function pass1()
{
	if($("input[name=password2]").val()!="" && $("input[name=password]").val()!=$("input[name=password2]").val())
	{
		$("#notmatch").toggle();
	}
}
</script>