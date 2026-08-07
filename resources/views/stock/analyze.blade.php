@extends('layouts.app')
@section('header', __('Stock Analysis'))

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header d-flex flex-column gap-2">
            <h3 class="card-title mb-0">Stock Analysis</h3>
            <div class="d-flex gap-1 align-items-center">
                <input list="idx-stocks" id="analysis-symbol" class="form-control form-control-sm" style="width:200px;"
                    placeholder="Kode saham, mis. BBCA" value="BBCA" autocomplete="off">
                <button id="analysis-load" class="btn btn-sm btn-primary" type="button">Cari</button>
                <button id="analysis-run" class="btn btn-sm btn-success" type="button">Analyze (AI)</button>
                <datalist id="idx-stocks">
                    @foreach ($stocks as $stock)
                        <option value="{{ $stock->code }}">{{ $stock->name }}</option>
                    @endforeach
                </datalist>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                {{-- Price + Volume chart --}}
                <div class="col-12">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Price & Volume</h5>
                        </div>
                        <div class="card-body">
                            <div id="analysis-chart" style="height:480px;"></div>
                        </div>
                    </div>
                </div>
                {{-- Broker summary --}}
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="card-title mb-0"><i class="fas fa-building me-1 text-secondary"></i> Broker Summary
                                <small class="text-muted fw-normal">Broksum</small>
                            </h5>
                            <div class="w-100">
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div>
                                        <span class="text-muted text-uppercase fw-semibold"
                                            style="font-size: 10px; letter-spacing: 0.3px;">Avg</span>
                                        <span class="fw-bold ms-1" style="font-size: 12px;">10,224</span>
                                    </div>
                                    <div class="border-end" style="height: 16px;"></div>
                                    <div>
                                        <span class="text-muted text-uppercase fw-semibold"
                                            style="font-size: 10px; letter-spacing: 0.3px;">Vol</span>
                                        <span class="fw-bold ms-1" style="font-size: 12px;">18,500 <small
                                                class="text-muted fw-normal">Lot</small></span>
                                    </div>
                                    <div class="border-end" style="height: 16px;"></div>
                                    <div>
                                        <span class="text-muted text-uppercase fw-semibold"
                                            style="font-size: 10px; letter-spacing: 0.3px;">Val</span>
                                        <span class="fw-bold ms-1" style="font-size: 12px;">Rp 189.2M</span>
                                    </div>
                                </div>
                            </div>
                            <div class="w-100 d-flex align-items-center justify-content-between flex-wrap gap-2 pt-2 mt-1 border-top">
                                <span
                                    class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded fw-bold"
                                    style="font-size: 10px;">
                                    ACCUMULATION
                                </span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="bgmodeSwitch"
                                        checked style="cursor: pointer;">
                                    <label class="form-check-label small fw-semibold text-secondary" for="bgmodeSwitch"
                                        style="cursor: pointer; user-select: none;">Net</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Filter bar --}}
                            <div class="row g-2 mb-2">
                                <div class="col-6 col-md-3">
                                    <label class="form-label mb-0 small text-muted">Start</label>
                                    <input type="date" class="form-control form-control-sm" value="{{ $tradeDay }}">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label mb-0 small text-muted">End</label>
                                    <input type="date" class="form-control form-control-sm" value="{{ $tradeDay }}">
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label mb-0 small text-muted">Investor</label>
                                    <select class="form-select form-select-sm">
                                        <option>All Investor</option>
                                        <option>Domestic Only (D)</option>
                                        <option>Foreign Only (F)</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label mb-0 small text-muted">Market</label>
                                    <select class="form-select form-select-sm">
                                        <option>All Market</option>
                                        <option>Regular (RG)</option>
                                        <option>Negotiated (NG)</option>
                                        <option>Cash (TN)</option>
                                    </select>
                                </div>
                            </div>
                            {{-- HAKA/HAKI net bar (Net mode only) --}}
                            <div id="bgHakaHaki" class="mb-3 px-1">
                                {{-- Header Info --}}
                                <div class="d-flex justify-content-between align-items-center mb-1"
                                    style="font-size: 0.75rem;">
                                    <span class="fw-semibold text-success d-flex align-items-center gap-1" <span>Buy
                                        58%</span>
                                    </span>
                                    <span class="fw-semibold text-danger d-flex align-items-center gap-1">
                                        <span>Sell 42%</span>
                                    </span>
                                </div>
                                {{-- Smooth Progress Bar --}}
                                <div class="progress bg-secondary-subtle p-0.5"
                                    style="height: 8px; border-radius: 999px; overflow: hidden; gap: 2px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: 58%; border-radius: 999px 0 0 999px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);"
                                        aria-valuenow="58" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                    <div class="progress-bar bg-danger" role="progressbar"
                                        style="width: 42%; border-radius: 0 999px 999px 0; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);"
                                        aria-valuenow="42" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2">
                                {{-- TOP BUYER --}}
                                <div class="col-md-6">
                                    <div class="text-success fw-bold mb-1" style="font-size:11px;">TOP BUYER (ACCUMULATOR)
                                    </div>
                                    <div style="max-height:240px;overflow:auto;">
                                    <table class="table table-sm table-hover mb-0"
                                        style="font-size:11px;font-family:monospace;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Broker</th>
                                                <th class="text-end">Vol</th>
                                                <th class="text-end">Val(M)</th>
                                                <th class="text-end">Avg</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge bg-warning text-dark">YP</span></td>
                                                <td class="text-end">5,200</td>
                                                <td class="text-end">53.2</td>
                                                <td class="text-end">10,227</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-primary">AK</span></td>
                                                <td class="text-end">4,100</td>
                                                <td class="text-end">41.9</td>
                                                <td class="text-end">10,229</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning text-dark">PD</span></td>
                                                <td class="text-end">3,300</td>
                                                <td class="text-end">33.7</td>
                                                <td class="text-end">10,224</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-primary">BK</span></td>
                                                <td class="text-end">3,000</td>
                                                <td class="text-end">30.7</td>
                                                <td class="text-end">10,220</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-primary">KZ</span></td>
                                                <td class="text-end">2,900</td>
                                                <td class="text-end">29.7</td>
                                                <td class="text-end">10,228</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                </div>
                                {{-- TOP SELLER --}}
                                <div class="col-md-6">
                                    <div class="text-danger fw-bold mb-1" style="font-size:11px;">TOP SELLER (DISTRIBUTOR)
                                    </div>
                                    <div style="max-height:240px;overflow:auto;">
                                    <table class="table table-sm table-hover mb-0"
                                        style="font-size:11px;font-family:monospace;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Broker</th>
                                                <th class="text-end">Vol</th>
                                                <th class="text-end">Val(M)</th>
                                                <th class="text-end">Avg</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="badge bg-primary">AZ</span></td>
                                                <td class="text-end">4,800</td>
                                                <td class="text-end">49.1</td>
                                                <td class="text-end">10,225</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning text-dark">XL</span></td>
                                                <td class="text-end">3,900</td>
                                                <td class="text-end">39.9</td>
                                                <td class="text-end">10,223</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-danger">NI</span></td>
                                                <td class="text-end">3,200</td>
                                                <td class="text-end">32.7</td>
                                                <td class="text-end">10,219</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-warning text-dark">CS</span></td>
                                                <td class="text-end">2,800</td>
                                                <td class="text-end">28.6</td>
                                                <td class="text-end">10,214</td>
                                            </tr>
                                            <tr>
                                                <td><span class="badge bg-primary">RX</span></td>
                                                <td class="text-end">2,400</td>
                                                <td class="text-end">24.5</td>
                                                <td class="text-end">10,225</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Done / detail transactions --}}
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="card-title mb-0">Done Detail Transactions</h5>
                            <div class="text-end" style="font-size:11px;font-family:monospace;">
                                <span class="fw-bold">BBCA</span>
                                &nbsp;10,225
                                &nbsp;<span class="text-danger">-25 / -0.2%</span>
                            </div>
                            <div class="w-100">
                                <div class="progress" style="height:6px;border-radius:0;overflow:hidden;">
                                    <div class="progress-bar bg-success rounded-0" style="width:58%">HAKA</div>
                                    <div class="progress-bar bg-danger rounded-0" style="width:42%">HAKI</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                {{-- BUY SIDE (HAKA) --}}
                                <div class="col-md-6">
                                    <div class="text-success fw-bold mb-1" style="font-size:11px;">HAKA / BUY DRIVEN</div>
                                    <div style="max-height:240px;overflow:auto;">
                                    <table class="table table-sm table-hover mb-0"
                                        style="font-size:11px;font-family:monospace;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Time</th>
                                                <th>Buyer(B)</th>
                                                <th>Seller(S)</th>
                                                <th>Price</th>
                                                <th>Vol</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>09:01:12</td>
                                                <td><span class="badge bg-secondary">YP</span></td>
                                                <td><span class="badge bg-light text-dark">CC</span></td>
                                                <td class="text-success">10,230</td>
                                                <td>1,200</td>
                                                <td><span class="text-info">M</span></td>
                                            </tr>
                                            <tr>
                                                <td>09:03:45</td>
                                                <td><span class="badge bg-secondary">AZ</span></td>
                                                <td><span class="badge bg-light text-dark">PD</span></td>
                                                <td class="text-success">10,235</td>
                                                <td>850</td>
                                                <td><span class="text-info">M</span></td>
                                            </tr>
                                            <tr>
                                                <td>09:07:30</td>
                                                <td><span class="badge bg-secondary">CC</span></td>
                                                <td><span class="badge bg-light text-dark">NH</span></td>
                                                <td class="text-success">10,228</td>
                                                <td>2,400</td>
                                                <td><span class="text-primary">F</span></td>
                                            </tr>
                                            <tr>
                                                <td>09:12:08</td>
                                                <td><span class="badge bg-secondary">PD</span></td>
                                                <td><span class="badge bg-light text-dark">YP</span></td>
                                                <td class="text-success">10,240</td>
                                                <td>600</td>
                                                <td><span class="text-info">M</span></td>
                                            </tr>
                                            <tr>
                                                <td>09:18:55</td>
                                                <td><span class="badge bg-secondary">AZ</span></td>
                                                <td><span class="badge bg-light text-dark">CC</span></td>
                                                <td class="text-success">10,225</td>
                                                <td>1,750</td>
                                                <td><span class="text-primary">F</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                </div>
                                {{-- SELL SIDE (HAKI) --}}
                                <div class="col-md-6">
                                    <div class="text-danger fw-bold mb-1" style="font-size:11px;">HAKI / SELL DRIVEN</div>
                                    <div style="max-height:240px;overflow:auto;">
                                    <table class="table table-sm table-hover mb-0"
                                        style="font-size:11px;font-family:monospace;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Time</th>
                                                <th>Price</th>
                                                <th>Vol</th>
                                                <th>Buyer(B)</th>
                                                <th>Seller(S)</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>09:02:30</td>
                                                <td class="text-danger">10,220</td>
                                                <td>900</td>
                                                <td><span class="badge bg-light text-dark">NH</span></td>
                                                <td><span class="badge bg-secondary">YP</span></td>
                                                <td><span class="text-info">M</span></td>
                                            </tr>
                                            <tr>
                                                <td>09:05:14</td>
                                                <td class="text-danger">10,215</td>
                                                <td>1,100</td>
                                                <td><span class="badge bg-light text-dark">PD</span></td>
                                                <td><span class="badge bg-secondary">AZ</span></td>
                                                <td><span class="text-primary">F</span></td>
                                            </tr>
                                            <tr>
                                                <td>09:09:47</td>
                                                <td class="text-danger">10,210</td>
                                                <td>500</td>
                                                <td><span class="badge bg-light text-dark">CC</span></td>
                                                <td><span class="badge bg-secondary">PD</span></td>
                                                <td><span class="text-info">M</span></td>
                                            </tr>
                                            <tr>
                                                <td>09:14:02</td>
                                                <td class="text-danger">10,205</td>
                                                <td>2,050</td>
                                                <td><span class="badge bg-light text-dark">AZ</span></td>
                                                <td><span class="badge bg-secondary">CC</span></td>
                                                <td><span class="text-primary">F</span></td>
                                            </tr>
                                            <tr>
                                                <td>09:20:39</td>
                                                <td class="text-danger">10,200</td>
                                                <td>700</td>
                                                <td><span class="badge bg-light text-dark">YP</span></td>
                                                <td><span class="badge bg-secondary">NH</span></td>
                                                <td><span class="text-info">M</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- PV analysis --}}
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Price-Volume Analysis (RYCAD / WYFCOOF)</h5>
                        </div>
                        <div class="card-body text-muted">Placeholder — PV signals from OHLC + volume.</div>
                    </div>
                </div>
                {{-- Technikal --}}
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Technikal Analysis</h5>
                        </div>
                        <div class="card-body text-muted">Placeholder — RSI / MACD / MA signals.</div>
                    </div>
                </div>
                {{-- Smart-money classification --}}
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Buyer/Seller Classification</h5>
                        </div>
                        <div class="card-body text-muted">Placeholder — bandar / emiten / smart money / ritel tags.</div>
                    </div>
                </div>
                {{-- Accumulation range + AVG lines --}}
                <div class="col-lg-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Accumulation Range & AVG Lines</h5>
                        </div>
                        <div class="card-body text-muted">Placeholder — AVG SM/bandar/emiten as support, AVG ritel as
                            resistance.</div>
                    </div>
                </div>
                {{-- AI analysis result --}}
                <div class="col-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h5 class="card-title mb-0">AI Analysis Result</h5>
                        </div>
                        <div class="card-body text-muted" id="analysis-ai-result">Placeholder — output from
                            trained/validated AI skill.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
