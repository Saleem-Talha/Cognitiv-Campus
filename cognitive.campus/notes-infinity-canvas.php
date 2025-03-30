<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Drawing Tool</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .color-picker {
            display: inline-block;
            width: 30px;
            height: 30px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="mb-3 mt-3">
        <div style="width: 100%; border:1px solid #000;">
            <canvas id="drawingCanvas" style="width: 100%; height: 500px;"></canvas>
        </div>
        <div class="mt-2 d-flex justify-content-between align-items-center">
            <div>
                <button type="button" id="penTool" class="btn btn-link text-dark"><i class='bx bx-pencil'></i></button>
                <button type="button" id="lineTool" class="btn btn-link text-dark"><i class='bx bx-minus'></i></button>
                <button type="button" id="squareTool" class="btn btn-link text-dark"><i class='bx bx-square'></i></button>
                <button type="button" id="circleTool" class="btn btn-link text-dark"><i class='bx bx-circle'></i></button>
                <button type="button" id="eraserTool" class="btn btn-link text-dark"><i class='bx bx-eraser'></i></button>
                <button type="button" id="undoButton" class="btn btn-link text-dark"><i class='bx bx-undo'></i></button>
                <button type="button" id="redoButton" class="btn btn-link text-dark"><i class='bx bx-redo'></i></button>
                <button type="button" id="colorPicker" class="btn btn-link text-dark p-0">
                    <input type="color" class="form-control form-control-color" value="#000000">
                </button>
            </div>
            
        </div>
        <div class="mt-2">
            <button type="button" id="insertDrawing" class="btn btn-outline-primary me-2">Insert Drawing</button>
                <button type="button" id="clearCanvas" class="btn btn-outline-primary"><i class='bx bx-trash'></i></button>
            </div>
    </div>

    <script src="js/infinity-canvas.js"></script>
</body>

</html>