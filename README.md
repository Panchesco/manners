#Manners
Tools for using ExpressionEngine image file manipulations with responsive images in the templates.

##Installation

###EE2
1. Download and unzip the package directory into /system/expressionengine/third_party/manners

###EE3
1. Download and unzip the package directory into /system/user/addons/manners
2. If you're using EE3, install the plugin in CP > Add-ons Manager

##Usage: Tag Pairs

###{exp:manners:srcset_wrap}
Add srcset string of image file manipulations to img tags wrapped in this tag pair.

<figure>
	<img src="http://panchesco.com/media/image-manipulations.png" alt="Screenshot of the File Manager /Edit Upload area">
	<figcaption><p><em>File manipulations are passed to the srcset attributes for images in your templates.</em></p></figcaption>
</figure>

####Parameters

| Parameter | Required? |	Description | Default | Options
| --- | --- | --- | --- | --- |
| directory_id | See notes | ID of directory to look for file manipulations in | | |
| directory_name | See notes | Name of directory to look for file manipulations in | | |
| short_names | No	| Pipe delimited list of short names of file manipulations <br>to include in the returned srcset |  |	|
| break_lines | No	| Break returned srcset string into lines? | y | y, n	|

###Notes

For srcset to be applied, either a valid directory_id or directory_name parameter must be present.


#####Example

Add a srcset to the img tags in a block of template content.

* In your template HTML, wrap the block of content you want the srcset attribute applied to img tags.
```
 {exp:channel:entries 
 	channel="blog" 
 	url_title="{segment_3}"
 	disable="pagination"}
  <article>
 {exp:manners:srcset_wrap
	directory_name="Source Set"
	short_names="phone|tablet|desktop|hdef"
	break_lines="y"}
	{custom_field}
	{/exp:manners:srcset_wrap}
 </article>
 {exp:channel:entries}

```

##Usage: Single Tags

###{exp:manners:srcset}
Add a srcset string of file manipulations to img tags wrapped in this tag.


####Parameters

| Parameter | Required? |	Description | Default | Options
| --- | --- | --- | --- | --- |
| file_id | Yes | file_id to return manipulations for | | |
| short_names | No	| Pipe delimited list of short names of file manipulations <br>to include in the returned srcset |  |	|
| break_lines | No	| Break returned srcset string into lines? | y | y, n	|

#####Example

Add srcset to an img tag in a template.

* In your template HTML, wrap an img tag in the tag pair for a custom field you uploaded an image to.
* Set the default file manipulation in the src attribute.
* Create a srset attribute and place the {exp:manners:srcset} in it. 
* Set the file_id and short_names parameters. 
 
```
 <div class="section-banner">
 	{myimage_custom_field}
 	<img src="{url:phone}"
 		srcset="{exp:manners:srcset file_id="{file_id}" short_names="{phone|tablet|desktop|hdef}" break_lines="y"}"
 		alt="Section Name">
 	{/myimage_custom_field}
 </div>

```
