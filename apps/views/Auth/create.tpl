<h1>Auth create<br /></h1>
<form action="<!----value:document_root---->Auth/save/" method="post">
  username:<input type="text" name="Auth[username]" length="64" value="<!----value:Auth:username---->"><br>
  password:<input type="text" name="Auth[password]" length="64" value=""><br>
  role_id:<input type="text" name="Auth[role_id]" length="64" value="<!----value:Auth:role_id---->"><br>
  email:<input type="text" name="Auth[email]" length="128" value="<!----value:Auth:email---->"><br>
  <input type="submit" name="bottom">
</form>
