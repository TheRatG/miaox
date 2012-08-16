<?php
$group = $this->_getvalueOf( 'group' );
$permission = $this->_getvalueOf( 'permission' );
?>
<section>
	<div class="page-header">
		<h1>Groups <small>Access list for resources</small></h1>
	</div>

	<h2>Labs</h2>
	<table id="access_group" class="table table-condensed table-bordered table-striped">
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th colspan="3">Groups</th>
			</tr>
			<tr>
				<th>#</th>
				<th>Resources</th>
				<?php foreach ($group as $item):?>
					<th><?=$item['name']?></th>
				<?php endforeach;?>
			</tr>
		</thead>
		<tbody>
			<?php $i = 0;
			foreach( $permission as $pItem ):
			$i++;
			?>
				<tr>
					<td><?=$i?></td>
					<td><?=$pItem['resource_name']?></td>
					<? foreach ( $pItem['groups'] as $gItem ):
						$classPostfix = ($gItem['is_allow']) ? 'ok' : 'off';
					?>
						<td><i class="icon-<?=$classPostfix?>" data-resource_id="<?=$pItem['resource_id']?>" data-group_id="<?=$gItem['group_id']?>"></i></td>
					<?php endforeach;?>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>
</section>