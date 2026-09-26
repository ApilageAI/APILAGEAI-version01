(function () {
  "use strict";

  function clamp(value, min, max) {
    return Math.max(min, Math.min(max, value));
  }

  function normalizeData(data) {
    if (!Array.isArray(data)) return [];
    return data.map(function (v) {
      var n = Number(v);
      return Number.isFinite(n) ? n : 0;
    });
  }

  function getDisplaySize(canvas) {
    var rect = canvas.getBoundingClientRect ? canvas.getBoundingClientRect() : { width: 0, height: 0 };
    var parent = canvas.parentElement;
    var parentWidth = parent ? parent.clientWidth : 0;
    var width = rect.width || canvas.clientWidth || parentWidth || canvas.width || 600;
    var height = rect.height || canvas.clientHeight || canvas.height || 200;
    if (height < 50) height = canvas.height || 200;
    if (width < 50) width = 600;
    return { width: width, height: height };
  }

  function prepareCanvas(canvas) {
    var ctx = canvas.getContext("2d");
    var dpr = window.devicePixelRatio || 1;
    var size = getDisplaySize(canvas);
    canvas.width = size.width * dpr;
    canvas.height = size.height * dpr;
    ctx.setTransform(1, 0, 0, 1, 0, 0);
    ctx.scale(dpr, dpr);
    return { ctx: ctx, width: size.width, height: size.height };
  }

  function drawLine(ctx, points, color, width) {
    if (!points.length) return;
    ctx.save();
    ctx.strokeStyle = color;
    ctx.lineWidth = width;
    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    for (var i = 1; i < points.length; i++) {
      ctx.lineTo(points[i].x, points[i].y);
    }
    ctx.stroke();
    ctx.restore();
  }

  function drawPoints(ctx, points, color) {
    ctx.save();
    ctx.fillStyle = color;
    for (var i = 0; i < points.length; i++) {
      ctx.beginPath();
      ctx.arc(points[i].x, points[i].y, 2.5, 0, Math.PI * 2);
      ctx.fill();
    }
    ctx.restore();
  }

  function drawGrid(ctx, area, steps) {
    ctx.save();
    ctx.strokeStyle = "rgba(23, 37, 84, 0.12)";
    ctx.lineWidth = 1;
    for (var i = 0; i <= steps; i++) {
      var y = area.top + (area.height / steps) * i;
      ctx.beginPath();
      ctx.moveTo(area.left, y);
      ctx.lineTo(area.left + area.width, y);
      ctx.stroke();
    }
    ctx.restore();
  }

  function drawAxes(ctx, area) {
    ctx.save();
    ctx.strokeStyle = "#172554";
    ctx.lineWidth = 1.5;
    ctx.beginPath();
    ctx.moveTo(area.left, area.top);
    ctx.lineTo(area.left, area.top + area.height);
    ctx.lineTo(area.left + area.width, area.top + area.height);
    ctx.stroke();
    ctx.restore();
  }

  function drawLabels(ctx, area, labels, min, max) {
    ctx.save();
    ctx.fillStyle = "#172554";
    ctx.font = "12px 'Plus Jakarta Sans', sans-serif";
    ctx.textAlign = "right";
    ctx.textBaseline = "middle";
    var steps = 4;
    for (var i = 0; i <= steps; i++) {
      var value = max - (max - min) * (i / steps);
      var y = area.top + (area.height / steps) * i;
      ctx.fillText(String(Math.round(value)), area.left - 6, y);
    }

    ctx.textAlign = "center";
    ctx.textBaseline = "top";
    var skip = Math.max(1, Math.ceil(labels.length / 6));
    for (var j = 0; j < labels.length; j += skip) {
      var x = area.left + (area.width / Math.max(1, labels.length - 1)) * j;
      ctx.fillText(String(labels[j]), x, area.top + area.height + 8);
    }
    ctx.restore();
  }

  function line(canvas, labels, data, options) {
    if (!canvas) return;
    var series = normalizeData(data);
    var labelList = Array.isArray(labels) ? labels : [];
    var opts = options || {};
    var setup = prepareCanvas(canvas);
    var ctx = setup.ctx;
    var width = setup.width;
    var height = setup.height;

    ctx.clearRect(0, 0, width, height);

    var padding = {
      left: 40,
      right: 20,
      top: 20,
      bottom: 34,
    };

    var area = {
      left: padding.left,
      top: padding.top,
      width: Math.max(10, width - padding.left - padding.right),
      height: Math.max(10, height - padding.top - padding.bottom),
    };

    var max = Math.max.apply(null, series);
    var min = Math.min.apply(null, series);
    if (!Number.isFinite(max)) {
      max = 1;
      min = 0;
    }
    if (max === min) {
      max = max + 1;
      min = Math.max(0, min - 1);
    }

    drawGrid(ctx, area, 4);
    drawAxes(ctx, area);

    var points = [];
    var total = Math.max(1, series.length - 1);
    for (var i = 0; i < series.length; i++) {
      var x = area.left + (area.width / total) * i;
      var t = (series[i] - min) / (max - min);
      var y = area.top + area.height - clamp(t, 0, 1) * area.height;
      points.push({ x: x, y: y });
    }

    if (points.length) {
      drawLine(ctx, points, opts.lineColor || "#172554", 2);
      drawPoints(ctx, points, opts.pointColor || "#0ea5e9");
    } else {
      ctx.save();
      ctx.fillStyle = "#64748b";
      ctx.font = "12px 'Plus Jakarta Sans', sans-serif";
      ctx.textAlign = "center";
      ctx.textBaseline = "middle";
      ctx.fillText("No data available", area.left + area.width / 2, area.top + area.height / 2);
      ctx.restore();
    }
    drawLabels(ctx, area, labelList, min, max);
  }

  window.ChartLite = { line: line };
})();
