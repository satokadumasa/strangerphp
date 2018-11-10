<h1>Board edit<br /></h1>
<form action="<!----value:document_root---->Board/save/" method="post">
  Board id<input type="text" name="Board[id]" length="255" value="<!----value:Board:id---->"><br>

  Board title<input type="text" name="Board[title]" length="128" value="<!----value:Board:title---->"><br>
  Board body<input type="text" name="Board[body]" length="3000" value="<!----value:Board:body---->"><br>
  <input type="submit" name="bottom">
</form>
