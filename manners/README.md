#Manners
Tools for using ExpressionEngine image file manipulations with responsive images in the templates.

##Usage: Single Tags

###{exp:manners:srcset}
Return a srcset string of a file's URL and width via the Consrain or Crop widths in an upload directory.


####Parameters

| Parameter | Required? |	Description | Default | Options
| --- | --- | --- | --- | --- |
| file_id | Yes | file_id to return manipulations for | | |
| short_names | No	| Pipe delimited list of short names of file manipulations <br>to include in the returned srcset |  |	|
| break_lines | No	| Break returned srcset string into lines? | y | y, n	|

#####Examples

Add srcset to an img tag in a template outputting an image file from a custom field.

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
