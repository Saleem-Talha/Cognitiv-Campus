const canvas = document.getElementById('drawingCanvas');
const ctx = canvas.getContext('2d');
let isDrawing = false;
let startX = 0;
let startY = 0;
let currentTool = 'pen';
let currentColor = '#000000';
let shapes = [];
let undoStack = [];
let redoStack = [];
let selectedShape = null;

// Set canvas size and initialize
function initializeCanvas() {
    canvas.width = canvas.clientWidth;
    canvas.height = canvas.clientHeight;
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    redrawCanvas();
}

// Initial setup and add event listeners
window.addEventListener('load', () => {
    initializeCanvas();
    window.addEventListener('resize', initializeCanvas);
});

// Set up canvas for drawing
canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseout', stopDrawing);

function startDrawing(e) {
    isDrawing = true;
    const rect = canvas.getBoundingClientRect();
    [startX, startY] = [e.clientX - rect.left, e.clientY - rect.top];
    if (currentTool === 'pen') {
        shapes.push({
            tool: 'pen',
            points: [{
                x: startX,
                y: startY
            }],
            color: currentColor
        });
    } else if (currentTool === 'eraser') {
        erase(e);
    } else {
        shapes.push({
            tool: currentTool,
            start: {
                x: startX,
                y: startY
            },
            end: {
                x: startX,
                y: startY
            },
            color: currentColor
        });
    }
    redoStack = []; // Clear redo stack when a new action is performed
}

function draw(e) {
    if (!isDrawing) return;

    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    switch (currentTool) {
        case 'pen':
            shapes[shapes.length - 1].points.push({
                x,
                y
            });
            break;
        case 'eraser':
            erase(e);
            break;
        case 'line':
        case 'square':
            shapes[shapes.length - 1].end = {
                x,
                y
            };
            break;
        case 'circle':
            const dx = x - startX;
            const dy = y - startY;
            shapes[shapes.length - 1].radius = Math.sqrt(dx * dx + dy * dy);
            break;
    }

    redrawCanvas();
}

function stopDrawing() {
    if (isDrawing) {
        isDrawing = false;
        undoStack.push([...shapes]);
    }
}

function erase(e) {
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    const eraserSize = 10;

    shapes = shapes.filter(shape => {
        if (shape.tool === 'pen') {
            shape.points = shape.points.filter(point =>
                Math.sqrt(Math.pow(point.x - x, 2) + Math.pow(point.y - y, 2)) > eraserSize
            );
            return shape.points.length > 1;
        } else {
            // For other shapes, we'll just check if the eraser touches their bounding box
            const [left, top, right, bottom] = getShapeBounds(shape);
            return !(x > left - eraserSize && x < right + eraserSize &&
                y > top - eraserSize && y < bottom + eraserSize);
        }
    });
}

function getShapeBounds(shape) {
    switch (shape.tool) {
        case 'line':
        case 'square':
            return [
                Math.min(shape.start.x, shape.end.x),
                Math.min(shape.start.y, shape.end.y),
                Math.max(shape.start.x, shape.end.x),
                Math.max(shape.start.y, shape.end.y)
            ];
        case 'circle':
            return [
                shape.start.x - shape.radius,
                shape.start.y - shape.radius,
                shape.start.x + shape.radius,
                shape.start.y + shape.radius
            ];
        default:
            return [0, 0, 0, 0];
    }
}

function redrawCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    shapes.forEach(drawShape);
}

function drawShape(shape) {
    ctx.beginPath();
    ctx.strokeStyle = shape.color;
    switch (shape.tool) {
        case 'pen':
            ctx.moveTo(shape.points[0].x, shape.points[0].y);
            shape.points.forEach(point => ctx.lineTo(point.x, point.y));
            break;
        case 'line':
            ctx.moveTo(shape.start.x, shape.start.y);
            ctx.lineTo(shape.end.x, shape.end.y);
            break;
        case 'square':
            ctx.rect(shape.start.x, shape.start.y,
                shape.end.x - shape.start.x, shape.end.y - shape.start.y);
            break;
        case 'circle':
            ctx.arc(shape.start.x, shape.start.y, shape.radius, 0, 2 * Math.PI);
            break;
    }
    ctx.stroke();
}

// Tool selection
document.getElementById('penTool').addEventListener('click', () => currentTool = 'pen');
document.getElementById('lineTool').addEventListener('click', () => currentTool = 'line');
document.getElementById('squareTool').addEventListener('click', () => currentTool = 'square');
document.getElementById('circleTool').addEventListener('click', () => currentTool = 'circle');
document.getElementById('eraserTool').addEventListener('click', () => currentTool = 'eraser');

// Color selection
const colorPicker = document.getElementById('colorPicker');
colorPicker.addEventListener('input', (e) => {
    currentColor = e.target.value;
});

// Undo and Redo
document.getElementById('undoButton').addEventListener('click', undo);
document.getElementById('redoButton').addEventListener('click', redo);

function undo() {
    if (undoStack.length > 0) {
        redoStack.push([...shapes]);
        shapes = undoStack.pop();
        redrawCanvas();
    }
}

function redo() {
    if (redoStack.length > 0) {
        undoStack.push([...shapes]);
        shapes = redoStack.pop();
        redrawCanvas();
    }
}

// Clear canvas
document.getElementById('clearCanvas').addEventListener('click', () => {
    undoStack.push([...shapes]);
    shapes = [];
    redoStack = [];
    redrawCanvas();
});

// Insert drawing into TinyMCE
document.getElementById('insertDrawing').addEventListener('click', () => {
    const imageData = canvas.toDataURL('image/png');
    tinymce.activeEditor.execCommand('mceInsertContent', false, `<img src="${imageData}" alt="Drawing">`);
});

// Shape manipulation (resize and reposition)
canvas.addEventListener('dblclick', selectShape);

function selectShape(e) {
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    selectedShape = shapes.find(shape => {
        const [left, top, right, bottom] = getShapeBounds(shape);
        return x >= left && x <= right && y >= top && y <= bottom;
    });

    if (selectedShape) {
        canvas.style.cursor = 'move';
    }
}

canvas.addEventListener('mousemove', moveShape);

function moveShape(e) {
    if (selectedShape && e.buttons === 1) {
        const dx = e.movementX;
        const dy = e.movementY;

        if (selectedShape.tool === 'pen') {
            selectedShape.points = selectedShape.points.map(point => ({
                x: point.x + dx,
                y: point.y + dy
            }));
        } else {
            selectedShape.start.x += dx;
            selectedShape.start.y += dy;
            selectedShape.end.x += dx;
            selectedShape.end.y += dy;
        }

        redrawCanvas();
    }
}

canvas.addEventListener('wheel', resizeShape);

function resizeShape(e) {
    if (selectedShape && e.ctrlKey) {
        e.preventDefault();
        const scaleFactor = e.deltaY > 0 ? 0.9 : 1.1;

        if (selectedShape.tool === 'pen') {
            const centerX = selectedShape.points.reduce((sum, p) => sum + p.x, 0) / selectedShape.points.length;
            const centerY = selectedShape.points.reduce((sum, p) => sum + p.y, 0) / selectedShape.points.length;

            selectedShape.points = selectedShape.points.map(point => ({
                x: centerX + (point.x - centerX) * scaleFactor,
                y: centerY + (point.y - centerY) * scaleFactor
            }));
        } else {
            const centerX = (selectedShape.start.x + selectedShape.end.x) / 2;
            const centerY = (selectedShape.start.y + selectedShape.end.y) / 2;

            selectedShape.start.x = centerX + (selectedShape.start.x - centerX) * scaleFactor;
            selectedShape.start.y = centerY + (selectedShape.start.y - centerY) * scaleFactor;
            selectedShape.end.x = centerX + (selectedShape.end.x - centerX) * scaleFactor;
            selectedShape.end.y = centerY + (selectedShape.end.y - centerY) * scaleFactor;

            if (selectedShape.tool === 'circle') {
                selectedShape.radius *= scaleFactor;
            }
        }

        redrawCanvas();
    }
}

canvas.addEventListener('mouseup', () => {
    if (selectedShape) {
        selectedShape = null;
        canvas.style.cursor = 'default';
    }
});

// Insert drawing into TinyMCE
document.getElementById('insertDrawing').addEventListener('click', () => {
const imageData = canvas.toDataURL('image/png');
const img = document.createElement('img');
img.src = imageData;
img.alt = 'Drawing';
tinymce.activeEditor.insertContent(img.outerHTML);
});