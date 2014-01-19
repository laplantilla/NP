<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		Raymond Benc
 * @package  		Module_Feed
 * @version 		$Id: content.html.php 6181 2013-06-27 17:28:12Z Raymond_Benc $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>	
{if !Phpfox::getService('profile')->timeline()}
	<div class="activity_feed_content">							
{/if}
	<div class="activity_feed_content_text{if isset($aFeed.comment_type_id) && $aFeed.comment_type_id == 'poll'} js_parent_module_feed_{$aFeed.comment_type_id}{/if}">
		{if !isset($aFeed.feed_mini) && !Phpfox::getService('profile')->timeline()}
			<div class="activity_feed_content_info">
                            {if $aFeed.type_ex_next=='ex'}
				{if !empty($aFeed.parent_module_id)} {phrase var='feed.shared'}{else}{if isset($aFeed.parent_user)} {img theme='layout/arrow.png' class='v_middle'} {$aFeed.parent_user|user:'parent_':'':50} {/if}{if !empty($aFeed.feed_info)} {$aFeed.feed_info}{/if}{/if}{$aFeed|user:'':'':50}
                            {/if}
                            {if $aFeed.type_ex_next!='ex'}
				{$aFeed|user:'':'':10}{if !empty($aFeed.parent_module_id)} {phrase var='feed.shared'}{else}{if isset($aFeed.parent_user)} {img theme='layout/arrow.png' class='v_middle'} {$aFeed.parent_user|user:'parent_':'':50} {/if}{if !empty($aFeed.feed_info)} {$aFeed.feed_info}{/if}{/if}
                            {/if}
			</div>
		{/if}

		{if !empty($aFeed.feed_mini_content)}
			<div class="activity_feed_content_status">
				<div class="activity_feed_content_status_left">
					<img src="{$aFeed.feed_icon}" alt="" class="v_middle" /> {$aFeed.feed_mini_content} 
				</div>
				<div class="activity_feed_content_status_right">
					{template file='feed.block.link'}
				</div>
				<div class="clear"></div>
			</div>
		{/if}

		{if isset($aFeed.feed_status) && (!empty($aFeed.feed_status) || $aFeed.feed_status == '0')}
			<div class="activity_feed_content_status">
                                {$aFeed.feed_status|feed_strip|shorten:200:'feed.view_more':true|split:55|max_line}	
				{if Phpfox::getParam('feed.enable_check_in') && Phpfox::getParam('core.google_api_key') != '' && isset($aFeed.location_name)} 
					<span class="js_location_name_hover" {if isset($aFeed.location_latlng) && isset($aFeed.location_latlng.latitude)}onmouseover="$Core.Feed.showHoverMap('{$aFeed.location_latlng.latitude}','{$aFeed.location_latlng.longitude}', this);"{/if}> - <a href="http://maps.google.com/maps?daddr={$aFeed.location_latlng.latitude},{$aFeed.location_latlng.longitude}" target="_blank">{phrase var='feed.at_location' location=$aFeed.location_name}</a>
					</span> 
				{/if}
                                {if isset($aFeed.np_checkin_name) && isset($aFeed.np_checkin_lng) && $aFeed.np_checkin_name!==""}
                                    <span class="np_checkin_button_toggle">
                                        - <a href="#" onclick="npShowMap(this, '{$aFeed.np_checkin_lat}', '{$aFeed.np_checkin_lng}'); return false;">
                                            {phrase var='feed.at_location' location=$aFeed.np_checkin_name}
                                        </a>
                                    </span>
                                {/if}

			</div>
		{/if}
                {if isset($aFeed.np_youtube_url) && $aFeed.np_youtube_url!==""}
                    <a href="{$aFeed.np_youtube_url}" class="np_youtube_preview_container" target="_blank">
                        <img src="{$aFeed.np_youtube_thumb}" />
                        <h3>{$aFeed.np_youtube_title}</h3>
                        <p>{$aFeed.np_youtube_desc}</p>
                    </a>
                {/if}
                <span><b>{$aFeed.np_category}</b></span>
		<div class="activity_feed_content_link">				
			{if $aFeed.type_id == 'friend' && isset($aFeed.more_feed_rows) && is_array($aFeed.more_feed_rows) && count($aFeed.more_feed_rows)}
				{foreach from=$aFeed.more_feed_rows item=aFriends}
					{$aFriends.feed_image}
				{/foreach}
				{$aFeed.feed_image}
			{else}
                        {if preg_match('/(http|https):\/\/(?:www\.)?youtube.com\/watch\?(?=.*v=\w+)(?:\S+)?/i',$aFeed.feed_status, $ytm)}
                            {if preg_match('/[\\?\\&]v=([^\\?\\&\s]+)/',$ytm[0], $ytid)}
                                <iframe width="100%" height="315" src="//www.youtube.com/embed/{$ytid[1]}?rel=0" frameborder="0" allowfullscreen></iframe>
                                <div class="clear"></div>
                            {/if}
                        {else}
                            {if !empty($aFeed.feed_image)}
                            <div class="activity_feed_content_image"{if isset($aFeed.feed_custom_width)} style="width:{$aFeed.feed_custom_width};"{/if}>
                                    {if is_array($aFeed.feed_image)}
                                            <ul class="activity_feed_multiple_image">
                                                    {foreach from=$aFeed.feed_image item=sFeedImage}
                                                            <li>{$sFeedImage}</li>
                                                    {/foreach}
                                            </ul>
                                            <div class="clear"></div>
                                    {else}
                                            <a href="{if isset($aFeed.feed_link_actual)}{$aFeed.feed_link_actual}{else}{$aFeed.feed_link}{/if}"{if !isset($aFeed.no_target_blank)} target="_blank"{/if} class="{if isset($aFeed.custom_css)} {$aFeed.custom_css} {/if}{if !empty($aFeed.feed_image_onclick)}{if !isset($aFeed.feed_image_onclick_no_image)}play_link {/if} no_ajax_link{/if}"{if !empty($aFeed.feed_image_onclick)} onclick="{$aFeed.feed_image_onclick}"{/if}{if !empty($aFeed.custom_rel)} rel="{$aFeed.custom_rel}"{/if}{if isset($aFeed.custom_js)} {$aFeed.custom_js} {/if}{if Phpfox::getParam('core.no_follow_on_external_links')} rel="nofollow"{/if}>{if !empty($aFeed.feed_image_onclick)}{if !isset($aFeed.feed_image_onclick_no_image)}<span class="play_link_img">{phrase var='feed.play'}</span>{/if}{/if}{$aFeed.feed_image}</a>						
                                    {/if}
                            </div>
                            {/if}
                        {/if}
			<div class="{if (!empty($aFeed.feed_content) || !empty($aFeed.feed_custom_html)) && empty($aFeed.feed_image)} activity_feed_content_no_image{/if}{if !empty($aFeed.feed_image)} activity_feed_content_float{/if}"{if isset($aFeed.feed_custom_width)} style="margin-left:{$aFeed.feed_custom_width};"{/if}>
				{if !empty($aFeed.feed_title)}
					{if isset($aFeed.feed_title_sub)}
						<span class="user_profile_link_span" id="js_user_name_link_{$aFeed.feed_title_sub|clean}">
					{/if}
					<a href="{if isset($aFeed.feed_link_actual)}{$aFeed.feed_link_actual}{else}{$aFeed.feed_link}{/if}" class="activity_feed_content_link_title"{if isset($aFeed.feed_title_extra_link)} target="_blank"{/if}{if Phpfox::getParam('core.no_follow_on_external_links')} rel="nofollow"{/if}>{$aFeed.feed_title|clean|split:30}</a>
					{if isset($aFeed.feed_title_sub)}
						</span>
					{/if}
					{if !empty($aFeed.feed_title_extra)}
						<div class="activity_feed_content_link_title_link">
							<a href="{$aFeed.feed_title_extra_link}" target="_blank"{if Phpfox::getParam('core.no_follow_on_external_links')} rel="nofollow"{/if}>{$aFeed.feed_title_extra|clean}</a>
						</div>
					{/if}
				{/if}			
				{if !empty($aFeed.feed_content)}
					<div class="activity_feed_content_display">
						{$aFeed.feed_content|feed_strip|shorten:200:'...'|split:55|max_line}				
					</div>
				{/if}
				{if !empty($aFeed.feed_custom_html)}
					<div class="activity_feed_content_display_custom">
						{$aFeed.feed_custom_html}
					</div>
				{/if}
				
				{if !empty($aFeed.parent_module_id)}
					{module name='feed.mini' parent_feed_id=$aFeed.parent_feed_id parent_module_id=$aFeed.parent_module_id}
				{/if}
				
			</div>	
			{if !empty($aFeed.feed_image)}
			<div class="clear"></div>
			{/if}		
			{/if}
		</div>
                
                <div class="np_checkin_map_container"></div>
                
	</div><!-- // .activity_feed_content_text -->

{if Phpfox::isMobile()}
<div style="padding-bottom:10px; color:#808080;">
	{$aFeed.time_stamp|convert_time:'feed.feed_display_time_stamp'}
</div>
{/if}

	{if isset($aFeed.feed_view_comment)}			
		{module name='feed.comment'}
	{else}
		{template file='feed.block.comment'}
	{/if}

	{if $aFeed.type_id != 'friend'}
		{if isset($aFeed.more_feed_rows) && is_array($aFeed.more_feed_rows) && count($aFeed.more_feed_rows)}
			{if $iTotalExtraFeedsToShow = count($aFeed.more_feed_rows)}{/if}
			<a href="#" class="activity_feed_content_view_more" onclick="$(this).parents('.js_feed_view_more_entry_holder:first').find('.js_feed_view_more_entry').show(); $(this).remove(); return false;">{phrase var='feed.see_total_more_posts_from_full_name' total=$iTotalExtraFeedsToShow full_name=$aFeed.full_name|shorten:40:'...'}</a>			
		{/if}			
	{/if}
{if !Phpfox::getService('profile')->timeline()}
	</div><!-- // .activity_feed_content -->
{/if}

