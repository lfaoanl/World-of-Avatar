var DEBUG = false;

function drawGrid(canvas, tilesize, c) {
    var color = c || "#ff0000";
            var cnv = document.getElementById(canvas);

            var gridOptions = {
                minorLines: {
                    separation: 5,
                    color: '#00FF00'
                },
                majorLines: {
                    separation: tilesize,
                    color: color
                }
            };

            //drawGridLines(cnv, gridOptions.minorLines);
            drawGridLines(cnv, gridOptions.majorLines);

            return;
        }

        function drawGridLines(cnv, lineOptions) {


            var iWidth = cnv.width;
            var iHeight = cnv.height;

            var ctx = cnv.getContext('2d');
            ctx.lineWidth=1;

            ctx.strokeStyle = lineOptions.color;
            ctx.strokeWidth = 1;

            ctx.beginPath();

            var iCount = null;
            var i = null;
            var x = null;
            var y = null;

            iCount = Math.floor(iWidth / lineOptions.separation);

            for (i = 1; i <= iCount; i++) {
                x = (i * lineOptions.separation);
                ctx.moveTo(x, 0);
                ctx.lineTo(x, iHeight);
                ctx.stroke();
            }


            iCount = Math.floor(iHeight / lineOptions.separation);

            for (i = 1; i <= iCount; i++) {
                y = (i * lineOptions.separation);
                ctx.moveTo(0, y);
                ctx.lineTo(iWidth, y);
                ctx.stroke();
            }

            ctx.closePath();

            return;
        }
