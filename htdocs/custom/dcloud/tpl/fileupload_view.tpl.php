<?php
/* Copyright (C) 2011-2017 Regis Houssin <regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
?>

<!-- START TEMPLATE FILE UPLOAD -->
<!-- The template to display files available for upload -->
<!-- Warning id on script is not W3C compliant and is reported as error by phpcs but it is required by fileupload plugin -->
<script id="template-upload" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-upload fade">
        <td class="preview"><span class="fade"></span></td>
        <td class="name"><span>{%=file.name%}</span></td>
        <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
        {% if (file.error) { %}
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else if (o.files.valid && !i) { %}
            <td>
                <div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="bar" style="width:0%;"></div></div>
            </td>
            <td class="start">{% if (!o.options.autoUpload) { %}
                <button class="btn btn-primary">
                    <i class="icon-upload icon-white"></i>
                    <span>{%=locale.fileupload.start%}</span>
                </button>
            {% } %}</td>
        {% } else { %}
            <td colspan="2"></td>
        {% } %}
        <td class="cancel">{% if (!i) { %}
            <button class="btn btn-warning">
                <i class="icon-ban-circle icon-white"></i>
                <span>{%=locale.fileupload.cancel%}</span>
            </button>
        {% } %}</td>
    </tr>
{% } %}
</script>
<!-- The template to display files available for download -->
<!-- Warning id on script is not W3C compliant and is reported as error by phpcs but it is required by jfilepload plugin -->
<script id="template-download" type="text/x-tmpl">
{% for (var i=0, file; file=o.files[i]; i++) { %}
    <tr class="template-download fade">
        {% if (file.error) { %}
            <td></td>
            <td class="name"><span>{%=file.name%}</span></td>
            <td class="size"><span>{%=o.formatFileSize(file.size)%}</span></td>
            <td class="error" colspan="2"><span class="label label-important">{%=locale.fileupload.error%}</span> {%=locale.fileupload.errors[file.error] || file.error%}</td>
        {% } else { %}
            <td class="preview">{% if (file.thumbnail_url) { %}
                <a href="{%=file.url%}" title="{%=file.name%}" rel="gallery" download="{%=file.name%}"><img src="{%=file.thumbnail_url%}"></a>
            {% } %}</td>
            <td class="name">
				{% if (file.is_dir) { %}
					<a href="#" id="{%=file.url%}" class="dcloud-directory" title="{%=file.name%}" rel="{% if (file.is_dir == 2) { %}parentfolder{% } else { %}folder{% } %}">
						<img src="<?php echo dol_buildpath('/dcloud/img/', 1); ?>{%=file.mime%}" border="0"> {%=file.name%}
					</a>
				{% } else { %}
					<a href="{%=file.url%}" title="{%=file.name%}" rel="{%=file.thumbnail_url&&'gallery'%}" download="{%=file.name%}">
						<img src="<?php echo DOL_URL_ROOT; ?>/theme/common/mime/{%=file.mime%}" border="0"> {%=file.name%}
					</a>
				{% } %}
            </td>
			<td align="right" class="size">{% if (!file.is_dir) { %}
            	<span>{%=o.formatFileSize(file.size)%}</span>
			{% } %}</td>
			<td align="right" class="date"><span>{%=file.date%}</span></td>
            <td colspan="2"></td>
        {% } %}
		<?php if (!empty($user->rights->dcloud->delete)) { ?>
        <td align="right" class="delete">
			{% if (file.is_dir < 2) { %}
            	<button class="btn btn-danger" data-type="{%=file.delete_type%}" data-url="{%=file.delete_url%}">
                	<i class="icon-trash icon-white"></i>
                	<span>{%=locale.fileupload.destroy%}</span>
            	</button>
			{% } %}
            <!-- <input type="checkbox" name="delete" value="1"> -->
        </td>
		<?php } ?>
    </tr>
{% } %}
</script>
<!-- END PHP TEMPLATE -->
