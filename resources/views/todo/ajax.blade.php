<legend>未完成的Items</legend>
<ul id="uncompletedItemsList" class="list-group">
    @foreach ($uncompletedTodos as $item)
        @include('todo.show')
    @endforeach
</ul>
<hr>
<legend>完成的Items</legend>
<ul id="completedItemsList" class="list-group">
    @foreach ($completedTodos as $item)
        @include('todo.show')
    @endforeach
</ul>