<?php 
/*project group settings*/
?>
<?php include '../../common/view/header.html.php';
 	  include '../../common/view/tablesorter.html.php';
 	  include '../../common/view/colorize.html.php';
 	  include '../../common/view/datepicker.html.php';
?>
<body>
	<table width="100%" id="proteam" style="border: 0">
	  <tr>
	  	<td valign="top">
	  		<div class="week-title"><?php echo $lang->plan->proteam;?></div>
		  		<div class="prot-add">
		  		<form method="post">
		  			<table width="100%" style="border: 0">
		  				<tr>
		  				<td><?php echo $lang->plan->team.'：'.html::input('team','',"class='text-2'");?></td>
		  				<td><?php echo $lang->plan->leader.'：'.html::select('leader',$users,'',"class='select-2'");?></td>
		  				<td><?php echo $lang->plan->auditor1.'：'.html::select('auditor1',$users,'',"class='select-2'");?></td>
		  				<td><?php echo $lang->plan->auditor2.'：'.html::select('auditor2',$users,'',"class='select-2'");?></td>
		  				<td><?php echo html::submitButton().html::resetButton().html::backButton();?></td>
		  				</tr>
		  			</table>
				</form>
	  		</div>
	  		<table width="100%" style="text-align: center;">
	  			<tr class='colhead'>
	  				<th></th>
	  				<th><?php echo $lang->plan->team;?></th>
	  				<th><?php echo $lang->plan->leader;?></th>
	  				<th><?php echo $lang->plan->auditor1;?></th>
	  				<th><?php echo $lang->plan->auditor2;?></th>
	  				<th><?php echo $lang->actions;?></th>
	  			</tr>
	  			<?php $i = 0;?>
	  			<?php foreach ($proteam as $info):?>
	  			<?php $i++ ;?>
	  			<tr>
	  				<td><?php echo $i ;?></td>
	  				<td><?php echo $info->team ;?></td>
	  				<td><?php echo $info->realname ;?></td>
	  				<td><?php echo $info->rel1 ;?></td>
	  				<td><?php echo $info->rel2 ;?></td>
	  				<td>
	  				<?php common::printIcon('plan', 'delete', "id=$info->id&module=proteam&date=0", '', 'list', '', "hiddenwin");?>
	  				</td>
	  			</tr>
	  			<?php endforeach;?>
	  		</table>
		</td>
		</tr>
	</table>
</body>
<?php include '../../common/view/footer.html.php';?>		   	