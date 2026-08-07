// Stock chart: lightweight-charts + lightweight-charts-drawing (jalur A).
// TV-parity: dark theme, volume pane, MACD (line+signal+hist+zero), RSI (70/30/50),
// MA 20/50/200 overlays, named legends. ponytail: OHLC from /stock/ohlc (Yahoo .JK / Invezgo later).
import { createChart, CandlestickSeries, LineSeries, HistogramSeries } from 'lightweight-charts';
import { DrawingManager, getToolRegistry } from 'lightweight-charts-drawing';

const container = document.getElementById('tradingview-chart');
const input = document.getElementById('tv-symbol');
const loadBtn = document.getElementById('tv-load');
const toolSel = document.getElementById('tv-tool');
const tfSel = document.getElementById('tv-tf');
const indSel = document.getElementById('tv-ind');
const fromInput = document.getElementById('tv-from');
const toInput = document.getElementById('tv-to');
const applyBtn = document.getElementById('tv-apply');

const chart = createChart(container, {
    autoSize: true,
    layout: {
        background: { color: '#131722' },
        textColor: '#d1d4dc',
        fontFamily: '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif',
    },
    grid: { vertLines: { color: 'rgba(42,46,57,0.6)' }, horzLines: { color: 'rgba(42,46,57,0.6)' } },
    rightPriceScale: { borderColor: '#2a2e39' },
    timeScale: { borderColor: '#2a2e39', timeVisible: true, secondsVisible: false },
    crosshair: { mode: 1 },
});

const series = chart.addSeries(CandlestickSeries, {
    upColor: '#26a69a', downColor: '#ef5350', borderVisible: false,
    wickUpColor: '#26a69a', wickDownColor: '#ef5350',
});

const manager = new DrawingManager();
manager.attach(chart, series, container);

// Panes: 0 price(+MA overlays), 1 volume, 2 MACD, 3 RSI.
const ma20 = chart.addSeries(LineSeries, { color: '#26a69a', lineWidth: 2, priceLineVisible: false, title: 'MA 20' }, 0);
const ma50 = chart.addSeries(LineSeries, { color: '#f5a623', lineWidth: 2, priceLineVisible: false, title: 'MA 50' }, 0);
const ma200 = chart.addSeries(LineSeries, { color: '#ef5350', lineWidth: 2, priceLineVisible: false, title: 'MA 200' }, 0);

const volume = chart.addSeries(HistogramSeries, { priceFormat: { type: 'volume' }, priceScaleId: '', title: 'Volume' }, 1);

const macdLine = chart.addSeries(LineSeries, { color: '#2962ff', lineWidth: 1, priceLineVisible: false, title: 'MACD 12 26 9' }, 2);
const macdSignal = chart.addSeries(LineSeries, { color: '#ff6d00', lineWidth: 1, priceLineVisible: false, title: 'Signal 9' }, 2);
const macdHist = chart.addSeries(HistogramSeries, { priceLineVisible: false, title: 'MACD Histogram' }, 2);
macdLine.createPriceLine({ price: 0, color: '#363a45', lineWidth: 1, lineStyle: 2, axisLabelVisible: false, title: '' });

const rsi = chart.addSeries(LineSeries, { color: '#9b59b6', lineWidth: 1, priceLineVisible: false, title: 'RSI 14' }, 3);
rsi.createPriceLine({ price: 70, color: '#787b86', lineWidth: 1, lineStyle: 2, axisLabelVisible: true, title: '70' });
rsi.createPriceLine({ price: 30, color: '#787b86', lineWidth: 1, lineStyle: 2, axisLabelVisible: true, title: '30' });
rsi.createPriceLine({ price: 50, color: '#363a45', lineWidth: 1, lineStyle: 2, axisLabelVisible: false, title: '' });

[ma20, ma50, ma200, macdLine, macdSignal, macdHist, rsi].forEach((s) => s.applyOptions({ visible: false }));

let rawData = [];

function currentSymbol() {
    const v = (input.value || '').trim().toUpperCase();
    return v.startsWith('IDX:') ? v.replace('IDX:', '') : (v || 'BBCA');
}

async function loadOhlc(symbol, interval) {
    const url = '/stock/ohlc?symbol=' + encodeURIComponent(symbol) + '&interval=' + encodeURIComponent(interval);
    const res = await fetch(url);
    if (!res.ok) return [];
    const data = await res.json();
    return Array.isArray(data) ? data : [];
}

function emaValues(data, period) {
    const out = [];
    const k = 2 / (period + 1);
    let prev;
    for (let i = 0; i < data.length; i++) {
        const price = data[i].close;
        prev = i === 0 ? price : price * k + prev * (1 - k);
        if (i >= period - 1) out.push({ time: data[i].time, value: prev });
    }
    return out;
}
function smaValues(data, period) {
    const out = [];
    let sum = 0;
    for (let i = 0; i < data.length; i++) {
        sum += data[i].close;
        if (i >= period) sum -= data[i - period].close;
        if (i >= period - 1) out.push({ time: data[i].time, value: sum / period });
    }
    return out;
}
function macdValues(data) {
    const ema12 = emaValues(data, 12);
    const ema26 = emaValues(data, 26);
    const e26map = new Map(ema26.map((d) => [d.time, d.value]));
    const macd = ema12.filter((d) => e26map.has(d.time)).map((d) => ({ time: d.time, value: d.value - e26map.get(d.time) }));
    const signal = emaValues(macd, 9);
    const sigMap = new Map(signal.map((d) => [d.time, d.value]));
    const hist = macd.map((d) => {
        const v = d.value - (sigMap.get(d.time) ?? d.value);
        return { time: d.time, value: v, color: v >= 0 ? '#26a69a' : '#ef5350' };
    });
    return { macd, signal, hist };
}
function rsiValues(data, period = 14) {
    const out = [];
    let gain = 0, loss = 0;
    for (let i = 1; i < data.length; i++) {
        const diff = data[i].close - data[i - 1].close;
        const g = Math.max(diff, 0), l = Math.max(-diff, 0);
        if (i <= period) {
            gain += g; loss += l;
            if (i === period) {
                gain /= period; loss /= period;
                const rs = loss === 0 ? 100 : gain / loss;
                out.push({ time: data[i].time, value: 100 - 100 / (1 + rs) });
            }
        } else {
            gain = (gain * (period - 1) + g) / period;
            loss = (loss * (period - 1) + l) / period;
            const rs = loss === 0 ? 100 : gain / loss;
            out.push({ time: data[i].time, value: 100 - 100 / (1 + rs) });
        }
    }
    return out;
}

function applyIndicators() {
    const sel = new Set(Array.from(indSel.selectedOptions).map((o) => o.value));
    ma20.applyOptions({ visible: sel.has('ma20') });
    ma50.applyOptions({ visible: sel.has('ma50') });
    ma200.applyOptions({ visible: sel.has('ma200') });
    macdLine.applyOptions({ visible: sel.has('macd') });
    macdSignal.applyOptions({ visible: sel.has('macd') });
    macdHist.applyOptions({ visible: sel.has('macd') });
    rsi.applyOptions({ visible: sel.has('rsi14') });
    volume.applyOptions({ visible: sel.has('volume') });

    if (sel.has('ma20')) ma20.setData(smaValues(rawData, 20));
    if (sel.has('ma50')) ma50.setData(smaValues(rawData, 50));
    if (sel.has('ma200')) ma200.setData(smaValues(rawData, 200));
    if (sel.has('macd')) {
        const m = macdValues(rawData);
        macdLine.setData(m.macd);
        macdSignal.setData(m.signal);
        macdHist.setData(m.hist);
    }
    if (sel.has('rsi14')) rsi.setData(rsiValues(rawData, 14));
}

function filterByDate(data) {
    const from = fromInput.value || null;
    const to = toInput.value || null;
    return data.filter((d) => (!from || d.time >= from) && (!to || d.time <= to));
}

async function saveDrawings() {
    const drawings = manager.serialize ? manager.serialize() : [];
    await fetch('/stock/drawings', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ symbol: currentSymbol(), drawings }),
    });
}
async function loadDrawings() {
    const res = await fetch('/stock/drawings?symbol=' + encodeURIComponent(currentSymbol()));
    const { drawings } = await res.json();
    if (Array.isArray(drawings)) drawings.forEach((d) => manager.addDrawing(d));
}

function reload() {
    loadOhlc(currentSymbol(), tfSel.value).then((data) => {
        rawData = filterByDate(data);
        series.setData(rawData);
        volume.setData(rawData.map((d) => ({
            time: d.time,
            value: d.close * 1000,
            color: d.close >= d.open ? 'rgba(38,166,154,0.5)' : 'rgba(239,83,80,0.5)',
        })));
        applyIndicators();
        chart.timeScale().fitContent();
        manager.clear?.();
        loadDrawings();
    });
}

toolSel?.addEventListener('change', () => { if (toolSel.value && manager.setActiveTool) manager.setActiveTool(toolSel.value); });
indSel?.addEventListener('change', applyIndicators);
applyBtn?.addEventListener('click', reload);
loadBtn?.addEventListener('click', reload);
input?.addEventListener('keydown', (e) => { if (e.key === 'Enter') reload(); });
fromInput?.addEventListener('change', reload);
toInput?.addEventListener('change', reload);

let saveTimer;
manager.on?.('change', () => { clearTimeout(saveTimer); saveTimer = setTimeout(saveDrawings, 800); });

reload();
