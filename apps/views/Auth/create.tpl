<h1>Auth create<br /></h1>
<form action="/Auth/save/" method="post">
  Auth username<input type="text" name="Auth[username]" length="64" value="<!----value:Auth:username---->"><br>
  Auth password<input type="text" name="Auth[password]" length="64" value=""><br>
  Auth role_id<input type="text" name="Auth[role_id]" length="64" value="<!----value:Auth:role_id---->"><br>
  Auth email<input type="text" name="Auth[email]" length="128" value="<!----value:Auth:email---->"><br>
  Auth notified_at<input type="text" name="Auth[notified_at]" length="64" value="<!----value:Auth:notified_at---->"><br>
  Auth authentication_key<input type="text" name="Auth[authentication_key]" length="128" value="<!----value:Auth:authentication_key---->"><br>
  <input type="submit" name="bottom">
</form>
