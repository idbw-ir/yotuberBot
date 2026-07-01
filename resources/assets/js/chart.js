/**
 * ============================================
 * Youtuber Bot - Chart Management
 * ============================================
 * نسخه: 2.0.0
 * 
 * مدیریت نمودارهای Chart.js شامل:
 * - ساخت نمودارهای مختلف
 * - بروزرسانی دیتا
 * - رنگ‌های پیش‌فرض
 * - Tooltip سفارشی
 * - Responsive support
 */

// ═══════════════════════════════════════════
// 1. رنگ‌های پیش‌فرض
// ═══════════════════════════════════════════

const CHART_COLORS = {
    // رنگ‌های اصلی
    primary: '#8b5cf6',
    secondary: '#3b82f6',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#06b6d4',
    
    // رنگ‌های اضافی
    purple: '#a855f7',
    pink: '#ec4899',
    orange: '#f97316',
    teal: '#14b8a6',
    indigo: '#6366f1',
    
    // رنگ‌های با شفافیت
    primaryLight: 'rgba(139, 92, 246, 0.2)',
    secondaryLight: 'rgba(59, 130, 246, 0.2)',
    successLight: 'rgba(16, 185, 129, 0.2)',
    warningLight: 'rgba(245, 158, 11, 0.2)',
    dangerLight: 'rgba(239, 68, 68, 0.2)',
    infoLight: 'rgba(6, 182, 212, 0.2)'
};

// پالت رنگی برای نمودارهای چندتایی
const CHART_PALETTE = [
    '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
    '#06b6d4', '#a855f7', '#ec4899', '#f97316', '#14b8a6'
];

// ═══════════════════════════════════════════
// 2. تنظیمات پیش‌فرض
// ═══════════════════════════════════════════

const CHART_DEFAULTS = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        duration: 800,
        easing: 'easeInOutQuart'
    },
    plugins: {
        legend: {
            display: true,
            position: 'top',
            labels: {
                color: 'rgba(255, 255, 255, 0.8)',
                font: {
                    family: 'Vazirmatn',
                    size: 12,
                    weight: '500'
                },
                usePointStyle: true,
                pointStyle: 'circle',
                padding: 20,
                boxWidth: 8,
                boxHeight: 8
            }
        },
        tooltip: {
            enabled: true,
            backgroundColor: 'rgba(15, 23, 42, 0.95)',
            titleColor: '#fff',
            bodyColor: 'rgba(255, 255, 255, 0.9)',
            borderColor: 'rgba(139, 92, 246, 0.3)',
            borderWidth: 1,
            cornerRadius: 8,
            padding: 12,
            titleFont: {
                family: 'Vazirmatn',
                size: 13,
                weight: '600'
            },
            bodyFont: {
                family: 'Vazirmatn',
                size: 12
            },
            displayColors: true,
            boxWidth: 8,
            boxHeight: 8,
            usePointStyle: true,
            boxPadding: 4
        }
    },
    scales: {
        x: {
            display: true,
            grid: {
                color: 'rgba(255, 255, 255, 0.05)',
                drawBorder: false
            },
            ticks: {
                color: 'rgba(255, 255, 255, 0.6)',
                font: {
                    family: 'Vazirmatn',
                    size: 11
                },
                maxRotation: 0,
                autoSkip: true,
                maxTicksLimit: 10
            }
        },
        y: {
            display: true,
            grid: {
                color: 'rgba(255, 255, 255, 0.05)',
                drawBorder: false
            },
            ticks: {
                color: 'rgba(255, 255, 255, 0.6)',
                font: {
                    family: 'Vazirmatn',
                    size: 11
                },
                padding: 8
            },
            beginAtZero: true
        }
    },
    interaction: {
        intersect: false,
        mode: 'index'
    }
};

// ═══════════════════════════════════════════
// 3. کلاس Chart Manager
// ═══════════════════════════════════════════

class ChartManager {
    constructor() {
        this.charts = new Map();
        this.defaultColors = CHART_PALETTE;
    }
    
    /**
     * ساخت نمودار خطی
     */
    createLineChart(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.error(`Canvas element with id "${canvasId}" not found`);
            return null;
        }
        
        const defaultOptions = {
            type: 'line',
            data: {
                labels: data.labels || [],
                datasets: data.datasets.map((dataset, index) => ({
                    label: dataset.label || `Dataset ${index + 1}`,
                    data: dataset.data || [],
                    borderColor: dataset.borderColor || this.defaultColors[index % this.defaultColors.length],
                    backgroundColor: dataset.backgroundColor || this.getColorWithOpacity(dataset.borderColor || this.defaultColors[index % this.defaultColors.length], 0.1),
                    borderWidth: 2,
                    tension: 0.4,
                    fill: dataset.fill !== false,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: dataset.borderColor || this.defaultColors[index % this.defaultColors.length],
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: dataset.borderColor || this.defaultColors[index % this.defaultColors.length],
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2
                }))
            },
            options: this.mergeOptions(CHART_DEFAULTS, options)
        };
        
        const chart = new Chart(ctx, defaultOptions);
        this.charts.set(canvasId, chart);
        
        return chart;
    }
    
    /**
     * ساخت نمودار میله‌ای
     */
    createBarChart(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.error(`Canvas element with id "${canvasId}" not found`);
            return null;
        }
        
        const defaultOptions = {
            type: 'bar',
            data: {
                labels: data.labels || [],
                datasets: data.datasets.map((dataset, index) => ({
                    label: dataset.label || `Dataset ${index + 1}`,
                    data: dataset.data || [],
                    backgroundColor: dataset.backgroundColor || this.getColorWithOpacity(this.defaultColors[index % this.defaultColors.length], 0.8),
                    borderColor: dataset.borderColor || this.defaultColors[index % this.defaultColors.length],
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }))
            },
            options: this.mergeOptions(CHART_DEFAULTS, options)
        };
        
        const chart = new Chart(ctx, defaultOptions);
        this.charts.set(canvasId, chart);
        
        return chart;
    }
    
    /**
     * ساخت نمودار دایره‌ای
     */
    createPieChart(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.error(`Canvas element with id "${canvasId}" not found`);
            return null;
        }
        
        const defaultOptions = {
            type: 'pie',
            data: {
                labels: data.labels || [],
                datasets: [{
                    data: data.data || [],
                    backgroundColor: data.backgroundColor || this.defaultColors.slice(0, (data.data || []).length),
                    borderColor: '#1f2937',
                    borderWidth: 3,
                    hoverOffset: 10
                }]
            },
            options: this.mergeOptions({
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgba(255, 255, 255, 0.8)',
                            font: {
                                family: 'Vazirmatn',
                                size: 12
                            },
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: CHART_DEFAULTS.plugins.tooltip
                }
            }, options)
        };
        
        const chart = new Chart(ctx, defaultOptions);
        this.charts.set(canvasId, chart);
        
        return chart;
    }
    
    /**
     * ساخت نمودار دونات
     */
    createDoughnutChart(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.error(`Canvas element with id "${canvasId}" not found`);
            return null;
        }
        
        const defaultOptions = {
            type: 'doughnut',
            data: {
                labels: data.labels || [],
                datasets: [{
                    data: data.data || [],
                    backgroundColor: data.backgroundColor || this.defaultColors.slice(0, (data.data || []).length),
                    borderColor: '#1f2937',
                    borderWidth: 3,
                    hoverOffset: 10,
                    cutout: '60%'
                }]
            },
            options: this.mergeOptions({
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgba(255, 255, 255, 0.8)',
                            font: {
                                family: 'Vazirmatn',
                                size: 12
                            },
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: CHART_DEFAULTS.plugins.tooltip
                }
            }, options)
        };
        
        const chart = new Chart(ctx, defaultOptions);
        this.charts.set(canvasId, chart);
        
        return chart;
    }
    
    /**
     * ساخت نمودار ترکیبی (Mixed)
     */
    createMixedChart(canvasId, data, options = {}) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) {
            console.error(`Canvas element with id "${canvasId}" not found`);
            return null;
        }
        
        const defaultOptions = {
            type: data.type || 'bar',
            data: {
                labels: data.labels || [],
                datasets: data.datasets.map((dataset, index) => ({
                    type: dataset.type || 'bar',
                    label: dataset.label || `Dataset ${index + 1}`,
                    data: dataset.data || [],
                    backgroundColor: dataset.backgroundColor || this.getColorWithOpacity(this.defaultColors[index % this.defaultColors.length], 0.8),
                    borderColor: dataset.borderColor || this.defaultColors[index % this.defaultColors.length],
                    borderWidth: 2,
                    borderRadius: dataset.type === 'bar' ? 8 : 0,
                    tension: dataset.type === 'line' ? 0.4 : 0,
                    fill: dataset.type === 'line' ? (dataset.fill !== false) : false,
                    yAxisID: dataset.yAxisID || 'y'
                }))
            },
            options: this.mergeOptions(CHART_DEFAULTS, options)
        };
        
        const chart = new Chart(ctx, defaultOptions);
        this.charts.set(canvasId, chart);
        
        return chart;
    }
    
    /**
     * بروزرسانی نمودار
     */
    updateChart(canvasId, newData) {
        const chart = this.charts.get(canvasId);
        if (!chart) {
            console.error(`Chart with id "${canvasId}" not found`);
            return false;
        }
        
        if (newData.labels) {
            chart.data.labels = newData.labels;
        }
        
        if (newData.datasets) {
            chart.data.datasets.forEach((dataset, index) => {
                if (newData.datasets[index]) {
                    if (newData.datasets[index].data) {
                        dataset.data = newData.datasets[index].data;
                    }
                    if (newData.datasets[index].label) {
                        dataset.label = newData.datasets[index].label;
                    }
                }
            });
        }
        
        chart.update('active');
        return true;
    }
    
    /**
     * نابود کردن نمودار
     */
    destroyChart(canvasId) {
        const chart = this.charts.get(canvasId);
        if (chart) {
            chart.destroy();
            this.charts.delete(canvasId);
            return true;
        }
        return false;
    }
    
    /**
     * دریافت نمودار
     */
    getChart(canvasId) {
        return this.charts.get(canvasId);
    }
    
    /**
     * نابود کردن همه نمودارها
     */
    destroyAll() {
        this.charts.forEach((chart, id) => {
            chart.destroy();
        });
        this.charts.clear();
    }
    
    /**
     * ادغام تنظیمات
     */
    mergeOptions(defaults, custom) {
        return {
            ...defaults,
            ...custom,
            plugins: {
                ...defaults.plugins,
                ...(custom.plugins || {})
            },
            scales: {
                ...defaults.scales,
                ...(custom.scales || {})
            }
        };
    }
    
    /**
     * دریافت رنگ با شفافیت
     */
    getColorWithOpacity(color, opacity) {
        if (color.startsWith('rgba')) {
            return color;
        }
        
        if (color.startsWith('#')) {
            const r = parseInt(color.slice(1, 3), 16);
            const g = parseInt(color.slice(3, 5), 16);
            const b = parseInt(color.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${opacity})`;
        }
        
        return color;
    }
}

// ═══════════════════════════════════════════
// 4. توابع کمکی
// ═══════════════════════════════════════════

/**
 * فرمت داده‌های نمودار برای نمایش فارسی
 */
function formatChartData(data) {
    return {
        labels: data.labels.map(label => {
            // تبدیل تاریخ به فرمت فارسی
            if (/^\d{4}-\d{2}-\d{2}$/.test(label)) {
                const date = new Date(label);
                return date.toLocaleDateString('fa-IR', { month: 'short', day: 'numeric' });
            }
            return label;
        }),
        datasets: data.datasets.map(dataset => ({
            ...dataset,
            data: dataset.data.map(value => {
                // فرمت اعداد بزرگ
                if (typeof value === 'number' && value >= 1000000) {
                    return value;
                }
                return value;
            })
        }))
    };
}

/**
 * ساخت داده‌های نمودار از API response
 */
function parseChartData(apiResponse) {
    if (!apiResponse || !apiResponse.data) {
        return null;
    }
    
    return {
        labels: apiResponse.data.labels || [],
        datasets: apiResponse.data.datasets || []
    };
}

/**
 * بروزرسانی نمودار از API
 */
async function updateChartFromAPI(canvasId, endpoint, params = {}) {
    try {
        const queryString = new URLSearchParams(params).toString();
        const url = queryString ? `${endpoint}?${queryString}` : endpoint;
        
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.data) {
            chartManager.updateChart(canvasId, data.data);
            return true;
        } else {
            throw new Error(data.error || 'خطا در دریافت داده‌ها');
        }
    } catch (error) {
        console.error('Error updating chart:', error);
        if (typeof showToast === 'function') {
            showToast(error.message || 'خطا در بروزرسانی نمودار', 'error');
        }
        return false;
    }
}

/**
 * ساخت Tooltip سفارشی
 */
function createCustomTooltip(context) {
    const tooltip = context.tooltip;
    
    if (tooltip.opacity === 0) {
        return;
    }
    
    // ساخت یا بروزرسانی tooltip element
    let tooltipEl = document.getElementById('chartjs-tooltip');
    
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'chartjs-tooltip';
        tooltipEl.className = 'fixed z-[100] pointer-events-none';
        tooltipEl.innerHTML = '<div class="bg-slate-900/95 backdrop-blur-sm border border-purple-500/30 rounded-lg p-3 shadow-2xl"></div>';
        document.body.appendChild(tooltipEl);
    }
    
    // تنظیم موقعیت
    const { chart } = context;
    const position = chart.canvas.getBoundingClientRect();
    
    tooltipEl.style.left = position.left + window.pageXOffset + tooltip.caretX + 'px';
    tooltipEl.style.top = position.top + window.pageYOffset + tooltip.caretY + 'px';
    tooltipEl.style.transform = 'translate(-50%, -100%)';
    
    // تنظیم محتوا
    const tooltipContent = tooltipEl.querySelector('div');
    
    if (tooltip.body) {
        const titleLines = tooltip.title || [];
        const bodyLines = tooltip.body.map(b => b.lines);
        
        let innerHtml = '<div class="space-y-2">';
        
        titleLines.forEach(title => {
            innerHtml += `<div class="text-white font-bold text-sm">${title}</div>`;
        });
        
        bodyLines.forEach((body, i) => {
            const colors = tooltip.labelColors[i];
            const bodyText = body.join('<br>');
            
            innerHtml += `
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full" style="background-color: ${colors.backgroundColor}"></span>
                    <span class="text-white/90 text-xs">${bodyText}</span>
                </div>
            `;
        });
        
        innerHtml += '</div>';
        tooltipContent.innerHTML = innerHtml;
    }
}

/**
 * افزودن Plugin برای نمایش مقدار در نمودار
 */
const dataLabelsPlugin = {
    id: 'dataLabels',
    afterDatasetsDraw(chart) {
        const { ctx } = chart;
        
        chart.data.datasets.forEach((dataset, datasetIndex) => {
            const meta = chart.getDatasetMeta(datasetIndex);
            
            if (!meta.hidden) {
                meta.data.forEach((element, index) => {
                    const data = dataset.data[index];
                    
                    ctx.save();
                    ctx.font = '11px Vazirmatn';
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.8)';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    
                    const formattedValue = typeof data === 'number' 
                        ? new Intl.NumberFormat('fa-IR').format(data)
                        : data;
                    
                    ctx.fillText(formattedValue, element.x, element.y - 10);
                    ctx.restore();
                });
            }
        });
    }
};

// ثبت Plugin
Chart.register(dataLabelsPlugin);

// ═══════════════════════════════════════════
// 5. نمودارهای آماده
// ═══════════════════════════════════════════

/**
 * ساخت نمودار رشد کاربران
 */
function createUserGrowthChart(canvasId, data) {
    return chartManager.createLineChart(canvasId, {
        labels: data.labels,
        datasets: [{
            label: 'کاربران جدید',
            data: data.data,
            borderColor: CHART_COLORS.primary,
            backgroundColor: CHART_COLORS.primaryLight,
            fill: true
        }]
    }, {
        plugins: {
            title: {
                display: true,
                text: 'رشد کاربران',
                color: '#fff',
                font: {
                    family: 'Vazirmatn',
                    size: 16,
                    weight: 'bold'
                },
                padding: 20
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    });
}

/**
 * ساخت نمودار درآمد
 */
function createRevenueChart(canvasId, data) {
    return chartManager.createMixedChart(canvasId, {
        labels: data.labels,
        datasets: [
            {
                type: 'bar',
                label: 'مبلغ دونیت (تومان)',
                data: data.amounts,
                backgroundColor: CHART_COLORS.successLight,
                borderColor: CHART_COLORS.success,
                yAxisID: 'y'
            },
            {
                type: 'line',
                label: 'تعداد دونیت',
                data: data.counts,
                borderColor: CHART_COLORS.secondary,
                backgroundColor: CHART_COLORS.secondaryLight,
                yAxisID: 'y1'
            }
        ]
    }, {
        scales: {
            y: {
                type: 'linear',
                display: true,
                position: 'right',
                beginAtZero: true
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'left',
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false
                }
            }
        }
    });
}

/**
 * ساخت نمودار وضعیت کاربران
 */
function createUserStatusChart(canvasId, data) {
    return chartManager.createDoughnutChart(canvasId, {
        labels: ['VIP', 'فعال', 'غیرفعال', 'بلاک شده'],
        data: [data.vip, data.active, data.inactive, data.blocked],
        backgroundColor: [
            CHART_COLORS.warning,
            CHART_COLORS.success,
            CHART_COLORS.info,
            CHART_COLORS.danger
        ]
    }, {
        cutout: '65%',
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    });
}

/**
 * ساخت نمودار فعالیت ساعتی
 */
function createHourlyActivityChart(canvasId, data) {
    return chartManager.createBarChart(canvasId, {
        labels: data.labels,
        datasets: [{
            label: 'تعداد پیام',
            data: data.data,
            backgroundColor: CHART_COLORS.primaryLight,
            borderColor: CHART_COLORS.primary
        }]
    }, {
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    });
}

// ═══════════════════════════════════════════
// 6. راه‌اندازی اولیه
// ═══════════════════════════════════════════

// ساخت ChartManager instance
const chartManager = new ChartManager();

// تنظیم Chart.js defaults
Chart.defaults.font.family = 'Vazirmatn';
Chart.defaults.color = 'rgba(255, 255, 255, 0.6)';
Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.05)';

// ═══════════════════════════════════════════
// 7. Export
// ═══════════════════════════════════════════

window.ChartManager = ChartManager;
window.chartManager = chartManager;
window.CHART_COLORS = CHART_COLORS;
window.CHART_PALETTE = CHART_PALETTE;
window.CHART_DEFAULTS = CHART_DEFAULTS;

// توابع کمکی
window.formatChartData = formatChartData;
window.parseChartData = parseChartData;
window.updateChartFromAPI = updateChartFromAPI;
window.createCustomTooltip = createCustomTooltip;

// نمودارهای آماده
window.createUserGrowthChart = createUserGrowthChart;
window.createRevenueChart = createRevenueChart;
window.createUserStatusChart = createUserStatusChart;
window.createHourlyActivityChart = createHourlyActivityChart;

// ═══════════════════════════════════════════
// پایان فایل
// ═══════════════════════════════════════════