// Broker inventory dual-pane chart. lightweight-charts v5 (ESM).
// ponytail: single widget mount; data injected as window.__INV__ from the blade component.
import { createChart, LineSeries } from 'lightweight-charts';

const cfg = window.__INV__;
if (cfg) {
    const dark = { background: { color: 'transparent' }, textColor: '#d1d4dc' };
    const light = { background: { color: 'transparent' }, textColor: '#3a3f4b' };

    // Top pane: price action line.
    const priceEl = document.getElementById('inv-price');
    const priceChart = createChart(priceEl, {
        autoSize: true, height: 180,
        layout: document.documentElement.dataset.bsTheme === 'dark' ? dark : light,
        grid: { vertLines: { color: 'rgba(128,128,128,0.15)' }, horzLines: { color: 'rgba(128,128,128,0.15)' } },
        rightPriceScale: { borderColor: 'rgba(128,128,128,0.3)' },
        timeScale: { borderColor: 'rgba(128,128,128,0.3)', timeVisible: false },
        crosshair: { mode: 1 },
    });
    const priceLine = priceChart.addSeries(LineSeries, { color: '#2962ff', lineWidth: 2, priceLineVisible: false, title: 'Price' });
    priceLine.setData(cfg.price);

    // Bottom pane: broker cumulative inventory.
    const invEl = document.getElementById('inv-inventory');
    const invChart = createChart(invEl, {
        autoSize: true, height: 220,
        layout: document.documentElement.dataset.bsTheme === 'dark' ? dark : light,
        grid: { vertLines: { color: 'rgba(128,128,128,0.15)' }, horzLines: { color: 'rgba(128,128,128,0.15)' } },
        rightPriceScale: { borderColor: 'rgba(128,128,128,0.3)' },
        timeScale: { borderColor: 'rgba(128,128,128,0.3)', timeVisible: false },
        crosshair: { mode: 1 },
    });

    // Zero baseline: flat line at value 0 separating accumulation (+) from distribution (−).
    const zero = invChart.addSeries(LineSeries, { color: '#787b86', lineWidth: 1, lineStyle: 2, priceLineVisible: false, lastValueVisible: false });
    zero.setData(cfg.dates.map((d) => ({ time: d, value: 0 })));

    // Per-broker inventory lines (green shades for accumulators, red for distributors),
    // keyed by code in seriesMap so the legend checkboxes can toggle visibility.
    const shades = { buy: ['#26a69a', '#2ebd85', '#37c871', '#43d98a'], sell: ['#ef5350', '#f0564f', '#f15a52', '#f25d54'] };
    const seriesMap = {};
    cfg.series.forEach((s, i) => {
        const palette = shades[s.side];
        const color = palette[i % palette.length];
        const line = invChart.addSeries(LineSeries, { color, lineWidth: 1, priceLineVisible: false, title: s.code });
        line.setData(s.data);
        seriesMap[s.code] = line;
    });

    // Net Bandar: sum of every broker's cumulative inventory per date, drawn with a
    // wider accent line plus a faint zero price-line for emphasis.
    const net = invChart.addSeries(LineSeries, {
        color: '#ffd166', lineWidth: 3, priceLineVisible: false, title: 'Net Bandar',
        // glow-ish via wider line + priceLine accent.
    });
    net.setData(cfg.dates.map((d, i) => ({ time: d, value: cfg.series.reduce((a, s) => a + s.data[i].value, 0) })));
    net.createPriceLine({ price: 0, color: 'rgba(255,209,102,0.25)', lineWidth: 1, lineStyle: 0, axisLabelVisible: false, title: '' });

    priceChart.timeScale().fitContent();
    invChart.timeScale().fitContent();

    // Legend checkbox toggles: show/hide the matching series (Net Bandar or per-broker).
    document.querySelectorAll('[data-inv-toggle]').forEach((cb) => {
        cb.addEventListener('change', () => {
            const code = cb.getAttribute('data-inv-toggle');
            if (code === '__NET__') net.applyOptions({ visible: cb.checked });
            else if (seriesMap[code]) seriesMap[code].applyOptions({ visible: cb.checked });
        });
    });
}
