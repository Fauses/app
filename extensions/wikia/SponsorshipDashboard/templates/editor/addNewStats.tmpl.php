<!-- s:<?= __FILE__ ?> -->
<!-- MAIN-PAGE -->

<form class="sd-form sd-sub-form">
	<input type="hidden" name="sourceType" value="Stats" />
	<a class="wikia-button sd-source-remove" style="float:right; margin-top:3px"> <img src="<?=f::app()->getGlobal('wgBlankImgUrl'); ?>" height="0" width="0" class="sprite close"> <?=wfMsg('sponsorship-dashboard-source-discard');?></a>
	<div class="sd-source-title"> <?=wfMsg('sponsorship-dashboard-source-datasource');?> <b>#1</b>: <?=wfMsg('sponsorship-dashboard-source-WikiaStats');?></div>
	<div style="font-weight:bold"> <?=wfMsg('sponsorship-dashboard-source-Variables');?> </div>
	<ul class="sd-list">
		<? $series = explode(',', $data[SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES] );  ?>
		<li> <input <? if( in_array( 'A', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>A" type="checkbox" class="sd-checkbox"><input class="sd-very-long" type="text" data-default="<?=wfMsg('sponsorship-dashboard-serie-A'); ?>" value="<?=wfMsg('sponsorship-dashboard-serie-A'); ?>" /></li>
		<li> <input <? if( in_array( 'B', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>B" type="checkbox" class="sd-checkbox"><?=wfMsg('sponsorship-dashboard-serie-B'); ?></li>
		<li> <input <? if( in_array( 'C', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>C" type="checkbox" class="sd-checkbox"><?=wfMsg('sponsorship-dashboard-serie-C'); ?></li>
		<li> <input <? if( in_array( 'D', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>D" type="checkbox" class="sd-checkbox"><?=wfMsg('sponsorship-dashboard-serie-D'); ?></li>
		<li> <input <? if( in_array( 'E', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>E" type="checkbox" class="sd-checkbox"><?=wfMsg('sponsorship-dashboard-serie-E'); ?></li>
		<li> <input <? if( in_array( 'F', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>F" type="checkbox" class="sd-checkbox"><?=wfMsg('sponsorship-dashboard-serie-F'); ?></li>
		<li> <input <? if( in_array( 'G', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>G" type="checkbox" class="sd-checkbox"><?=wfMsg('sponsorship-dashboard-serie-G'); ?></li>
		<li> <input <? if( in_array( 'H', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>H" type="checkbox" class="sd-checkbox"><?=wfMsg('sponsorship-dashboard-serie-H'); ?></li>
		<li> <input <? if( in_array( 'I', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>I" type="checkbox" class="sd-checkbox"><?=wfMsg('sponsorship-dashboard-serie-I'); ?></li>
		<li> <input <? if( in_array( 'J', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>J" type="checkbox" class="sd-checkbox"><?=wfMsg('sponsorship-dashboard-serie-J'); ?></li>
		<li> <input <? if( in_array( 'K', $series ) ) echo 'checked="checked"'; ?> name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_SERIES ;?>K" type="checkbox" class="sd-checkbox"><?=wfMsg('sponsorship-dashboard-serie-K'); ?></li>
	</ul>
	<div>
		<?=wfMsg('sponsorship-dashboard-source-pageviews-namespaces');?> <input name="<?=SponsorshipDashboardSourceStats::SD_PARAMS_STATS_NAMESPACES ;?>" type="text" value="" class="sd-long" style="left:200px;">
	</div>
	<div class="sd-source-line"></div>
	<div style="font-weight:bold"> <?=wfMsg('sponsorship-dashboard-source-wikis');?> </div>
	<ul class="sd-list">
		<li>
			<input <? if ( $data[ SponsorshipDashboardSource::SD_PARAMS_REP_SOURCE_TYPE ] == SponsorshipDashboardSource::SD_SOURCE_LIST ) echo 'checked="checked"' ?> name="<?=SponsorshipDashboardSource::SD_PARAMS_REP_SOURCE_TYPE; ?>" value="<?=SponsorshipDashboardSource::SD_SOURCE_LIST;?>" type="radio" class="sd-checkbox"> <?=wfMsg('sponsorship-dashboard-source-list');?> <input name="<?=SponsorshipDashboardSource::SD_PARAMS_REP_CITYID; ?>" type="text" value="<?=$data[SponsorshipDashboardSource::SD_PARAMS_REP_CITYID]; ?>" class="validate-list sd-long">
		</li>
		<li>
			<input <? if ( $data[ SponsorshipDashboardSource::SD_PARAMS_REP_SOURCE_TYPE ] == SponsorshipDashboardSource::SD_SOURCE_COMPETITORS ) echo 'checked="checked"' ?> name="<?=SponsorshipDashboardSource::SD_PARAMS_REP_SOURCE_TYPE; ?>" value="<?=SponsorshipDashboardSource::SD_SOURCE_COMPETITORS;?>" type="radio" class="sd-checkbox">  <?=wfMsg('sponsorship-dashboard-top-x-competitors');?>
			<div style="display:inline-block; width:600px; margin-left: 50px;" >
				<?=wfMsg('sponsorship-dashboard-source-number-of-competitors');?> <input name="<?=SponsorshipDashboardSource::SD_PARAMS_REP_TOPX; ?>" type="text" class="sd-short validate-number min1" value="<?=$data[SponsorshipDashboardSource::SD_PARAMS_REP_TOPX]; ?>" style="display:inline-block; margin-right:20px;" >
				<?=wfMsg('sponsorship-dashboard-source-wiki-id');?> <input name="<?=SponsorshipDashboardSource::SD_PARAMS_REP_COMP_CITYID; ?>" type="text" value="<?=$data[SponsorshipDashboardSource::SD_PARAMS_REP_COMP_CITYID]; ?>" class="sd-short validate-number" style="margin-right:20px;" >
				<?=wfMsg('sponsorship-dashboard-source-hub-id');?>
				<select name="<?=SponsorshipDashboardSource::SD_PARAMS_REP_COMP_HUBID; ?>" style="position:relative; left:0px; width: 100px; margin-right:20px;">
					<option value=""><?=wfMsg('sponsorship-dashboard-source-none');?></option>
				<? foreach( $hubs as $hubId => $hubName ){
					?><option value="<?=$hubId;?>" <? if( $data[ SponsorshipDashboardSource::SD_PARAMS_REP_COMP_HUBID ] == $hubId ) echo 'selected="selected"' ?>><?=$hubName;?></option><?
				} ?>
				</select>
			</div>
		</li>
	</ul>
</form>

<!-- END OF MAIN-PAGE -->
<!-- e:<?= __FILE__ ?> -->
