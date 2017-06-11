<h1>Auth edit<br /></h1>
<form action="<!----value:document_root---->Auth/save/" method="post">
  Auth id<input type="text" name="Auth[id]" length="255" value="<!----value:Auth:id---->"><br>

  Auth username<input type="text" name="Auth[username]" length="64" value="<!----value:Auth:username---->"><br>
  Auth password<input type="text" name="Auth[password]" length="64" value=""><br>
  Auth role_id<input type="text" name="Auth[role_id]" length="64" value="<!----value:Auth:role_id---->"><br>
  Auth email<input type="text" name="Auth[email]" length="128" value="<!----value:Auth:email---->"><br>
  Auth notified_at:<!----value:Auth:notified_at----><br>
  <!----value:Auth:authentication_key---->"><br>
  <input type="submit" name="bottom">
</form>
