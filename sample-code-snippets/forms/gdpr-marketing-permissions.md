As of Mailchimp for WordPress version 4.9.2 a field named `MARKETING_PERMISSIONS` is supported.
It allows you to enable certain marketing permissions on the selected Mailchimp lists by either their name or their unique ID (which you can find on the lists overview page in the plugin settings).

The field accepts a comma separated list of permission names or ID's.

#### Checkboxes

By ID:

```html 
<label><input type="checkbox" name="MARKETING_PERMISSIONS[]" value="38e6f999cf" /> Email</label>
<label><input type="checkbox" name="MARKETING_PERMISSIONS[]" value="1283a73736" /> Direct</label>
```

By name(s):

```html 
<label><input type="checkbox" name="MARKETING_PERMISSIONS[]" value="Email" /> Email</label>
<label><input type="checkbox" name="MARKETING_PERMISSIONS[]" value="Direct" /> Direct</label>
```

Note that if referring to permissions by their name that the `value` attribute has to be an exact match of whatever the name of the permission is in Mailchimp.

#### Hidden field

By name(s):

```html
<input type="hidden" name="MARKETING_PERMISSIONS" value="Customized Online Advertising,Email" />
```

By ID:

```html
<input type="hidden" name="MARKETING_PERMISSIONS" value="38e6f999cf" />
```
