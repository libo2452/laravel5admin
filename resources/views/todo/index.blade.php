<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Todo App</title>

    <!-- Bootstrap -->
    {{--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">--}}
    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <!--<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>-->
    {{--<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>--}}
    {{--<![endif]-->--}}
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-sm-offset-4 col-sm-4">
            <h1 class="text-center">Todo App</h1>
            <form id="addFrm" role="form">
                <div class="form-group">
                    <input type="text" class="form-control" name="title"  id="title" required="required" placeholder="Enter title">
                </div>
                <div class="form-group">
                    <input type="submit" class="btn btn-default" name="submit" value="Add">
                </div>
            </form>
            <hr>
            <div id="itemsList">
            </div>
        </div>
    </div>
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<!-- 新 Bootstrap 核心 CSS 文件 -->
<!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
<!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
<script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
{{--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>--}}
{{--<script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>--}}
<!-- Include all compiled plugins (below), or include individual files as needed -->
{{--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>--}}
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
<script>
    //renders item's new state to the page
    function addItem(id, isCompleted) {//根据状态添加item
        $.get("/items/" + id, function(data) {
            if (isCompleted) {
                $("#completedItemsList").append(data);
            } else {
                $("#uncompletedItemsList").append(data);
            }
        });
    }

    //removes item's old state from the page
    function removeItem(id) {
        $('li[data-id="' + id + '"').remove();
    }

    (function($, addItem, removeItem) {
        $.get( "/todo", function( data ) {//DOM加载后,AJAX请求数据,进入ItemController::index()
            $( "#itemsList" ).html( data );
        });

        $( "#addFrm" ).submit(function() {//回车或点击提交按钮时,AJAX post到ItemController::store()方法,json返回保存的'id'=>$item->id
            console.log($(this).serialize());
            $.post( "/todo", $(this).serialize(), function( data ) {
                addItem(data.id, false);
                $( "#title" ).val('');
            });
            return false;
        });

        $(document).on("change", ".isCompleted", function() {
            var id = $(this).closest('li').data('id');
            var isCompleted = $(this).prop("checked") ? 1 : 0;//获取该item的完成状态
            $.ajax('/todo/' + id, {//进入ItemController::update(),更细下item状态
                data: {"isCompleted": isCompleted},
                method: 'PATCH',
                success: function() {//根据状态变化删除增加item
                    removeItem(id);
                    addItem(id, isCompleted);
                }
            });
        });

        $(document).on("click", ".deleteItem", function() {
            var id = $(this).closest('li').data('id');
            $.ajax('/todo/' + id, {//进入ItemController::destroy()删除数据库中item
                method: 'DELETE',
                success: function() {//UI删除该item
                    removeItem(id);
                }
            });
        });
    })(jQuery, addItem, removeItem);

</script>
</body>
</html>
