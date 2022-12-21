<form method="post">
<input type="text" name="name"/>
<input class="btn btn-primary" type="submit" value="search"/>
</form>
<br/>
<br/>
<table class="table table-primary table-bordered table-striped">
<thead>
<tr>
<td>trnid</td>
<td>topup</td>
<td>bonus</td>
<td>allocation</td>
<td>&nbsp;</td>
</tr>
</thead>
<tbody>
<?php if(isset($listing) && count($listing)>0){?>
<?php $amount = 0; ?>
<?php foreach($listing as $key=>$value){ ?>
<tr>
<td><?php echo $value["trn_id"];?></td>
<td><?php echo number_format($value["topup"],2,".",""); $amount += floatval($value["topup"]); ?></td>
<td><?php echo number_format($value["bonus"],2,".",""); $amount += floatval($value["bonus"])?></td>
<td><?php echo number_format($value["allocation"],2,".",""); $amount -= floatval($value["allocation"])?></td>
<td><?php echo number_format($amount,2,".","");?></td>
</tr>
<?php }?>
<tr>
<td colspan="3"></td>
<th>Last balance</th>
<th><?php echo number_format($amount,2,".","");?></th>
</tr>
<?php } ?>
</tbody>
</table>