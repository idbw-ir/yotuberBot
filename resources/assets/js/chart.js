/**
 * ============================================
 * Youtuber Bot - Chart Management
 * ============================================
 * Ù†Ø³Ø®Ù‡: 2.1.0
 * 
 * Ù…Ø¯ÛŒØ±ÛŒØª Ù†Ù…ÙˆØ¯Ø§Ø±Ù‡Ø§ÛŒ Chart.js Ø´Ø§Ù…Ù„:
 * - Ø³Ø§Ø®Øª Ù†Ù…ÙˆØ¯Ø§Ø±Ù‡Ø§ÛŒ Ù…Ø®ØªÙ„Ù
 * - Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ Ø¯ÛŒØªØ§
 * - Ø±Ù†Ú¯â€ŒÙ‡Ø§ÛŒ Ù¾ÛŒØ´â€ŒÙØ±Ø¶
 * - Tooltip Ø³ÙØ§Ø±Ø´ÛŒ
 * - Responsive support
 */

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 1. Ø±Ù†Ú¯â€ŒÙ‡Ø§ÛŒ Ù¾ÛŒØ´â€ŒÙØ±Ø¶
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

const CHART_COLORS = {
    // Ø±Ù†Ú¯â€ŒÙ‡Ø§ÛŒ Ø§ØµÙ„ÛŒ
    primary: '#8b5cf6',
    secondary: '#3b82f6',
    success: '#10b981',
    warning: '#f59e0b',
    danger: '#ef4444',
    info: '#06b6d4',
    
    // Ø±Ù†Ú¯â€ŒÙ‡Ø§ÛŒ Ø§Ø¶Ø§ÙÛŒ
    purple: '#a855f7',
    pink: '#ec4899',
    orange: '#f97316',
    teal: '#14b8a6',
    indigo: '#6366f1',
    
    // Ø±Ù†Ú¯â€ŒÙ‡Ø§ÛŒ Ø¨Ø§ Ø´ÙØ§ÙÛŒØª
    primaryLight: 'rgba(139, 92, 246, 0.2)',
    secondaryLight: 'rgba(59, 130, 246, 0.2)',
    successLight: 'rgba(16, 185, 129, 0.2)',
    warningLight: 'rgba(245, 158, 11, 0.2)',
    dangerLight: 'rgba(239, 68, 68, 0.2)',
    infoLight: 'rgba(6, 182, 212, 0.2)'
};

// Ù¾Ø§Ù„Øª Ø±Ù†Ú¯ÛŒ Ø¨Ø±Ø§ÛŒ Ù†Ù…ÙˆØ¯Ø§Ø±Ù‡Ø§ÛŒ Ú†Ù†Ø¯ØªØ§ÛŒÛŒ
const CHART_PALETTE = [
    '#8b5cf6', '#3b82f6', '#10b981', '#f59e0b', '#ef4444',
    '#06b6d4', '#a855f7', '#ec4899', '#f97316', '#14b8a6'
];

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 2. ØªÙ†Ø¸ÛŒÙ…Ø§Øª Ù¾ÛŒØ´â€ŒÙØ±Ø¶
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 3. Ú©Ù„Ø§Ø³ Chart Manager
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

class ChartManager {
    constructor() {
        this.charts = new Map();
        this.defaultColors = CHART_PALETTE;
    }
    
    /**
     * Ø³Ø§Ø®Øª Ù†Ù…ÙˆØ¯Ø§Ø± Ø®Ø·ÛŒ
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
     * Ø³Ø§Ø®Øª Ù†Ù…ÙˆØ¯Ø§Ø± Ù…ÛŒÙ„Ù‡â€ŒØ§ÛŒ
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
     * Ø³Ø§Ø®Øª Ù†Ù…ÙˆØ¯Ø§Ø± Ø¯Ø§ÛŒØ±Ù‡â€ŒØ§ÛŒ
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
     * Ø³Ø§Ø®Øª Ù†Ù…ÙˆØ¯Ø§Ø± Ø¯ÙˆÙ†Ø§Øª
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
     * Ø³Ø§Ø®Øª Ù†Ù…ÙˆØ¯Ø§Ø± ØªØ±Ú©ÛŒØ¨ÛŒ (Mixed)
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
     * Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ Ù†Ù…ÙˆØ¯Ø§Ø±
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
     * Ù†Ø§Ø¨ÙˆØ¯ Ú©Ø±Ø¯Ù† Ù†Ù…ÙˆØ¯Ø§Ø±
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
     * Ø¯Ø±ÛŒØ§ÙØª Ù†Ù…ÙˆØ¯Ø§Ø±
     */
    getChart(canvasId) {
        return this.charts.get(canvasId);
    }
    
    /**
     * Ù†Ø§Ø¨ÙˆØ¯ Ú©Ø±Ø¯Ù† Ù‡Ù…Ù‡ Ù†Ù…ÙˆØ¯Ø§Ø±Ù‡Ø§
     */
    destroyAll() {
        this.charts.forEach((chart, id) => {
            chart.destroy();
        });
        this.charts.clear();
    }
    
    /**
     * Ø§Ø¯ØºØ§Ù… ØªÙ†Ø¸ÛŒÙ…Ø§Øª
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
     * Ø¯Ø±ÛŒØ§ÙØª Ø±Ù†Ú¯ Ø¨Ø§ Ø´ÙØ§ÙÛŒØª
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 4. ØªÙˆØ§Ø¨Ø¹ Ú©Ù…Ú©ÛŒ
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * ÙØ±Ù…Øª Ø¯Ø§Ø¯Ù‡â€ŒÙ‡Ø§ÛŒ Ù†Ù…ÙˆØ¯Ø§Ø± Ø¨Ø±Ø§ÛŒ Ù†Ù…Ø§ÛŒØ´ ÙØ§Ø±Ø³ÛŒ
 */
function formatChartData(data) {
    return {
        labels: data.labels.map(label => {
            // ØªØ¨Ø¯ÛŒÙ„ ØªØ§Ø±ÛŒØ® Ø¨Ù‡ ÙØ±Ù…Øª ÙØ§Ø±Ø³ÛŒ
            if (/^\d{4}-\d{2}-\d{2}$/.test(label)) {
                const date = new Date(label);
                return date.toLocaleDateString('fa-IR', { month: 'short', day: 'numeric' });
            }
            return label;
        }),
        datasets: data.datasets.map(dataset => ({
            ...dataset,
            data: dataset.data.map(value => {
                // ÙØ±Ù…Øª Ø§Ø¹Ø¯Ø§Ø¯ Ø¨Ø²Ø±Ú¯
                if (typeof value === 'number' && value >= 1000000) {
                    return value;
                }
                return value;
            })
        }))
    };
}

/**
 * Ø³Ø§Ø®Øª Ø¯Ø§Ø¯Ù‡â€ŒÙ‡Ø§ÛŒ Ù†Ù…ÙˆØ¯Ø§Ø± Ø§Ø² API response
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
 * Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ Ù†Ù…ÙˆØ¯Ø§Ø± Ø§Ø² API
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
            throw new Error(data.error || 'Ø®Ø·Ø§ Ø¯Ø± Ø¯Ø±ÛŒØ§ÙØª Ø¯Ø§Ø¯Ù‡â€ŒÙ‡Ø§');
        }
    } catch (error) {
        console.error('Error updating chart:', error);
        if (typeof showToast === 'function') {
            showToast(error.message || 'Ø®Ø·Ø§ Ø¯Ø± Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ Ù†Ù…ÙˆØ¯Ø§Ø±', 'error');
        }
        return false;
    }
}

/**
 * Ø³Ø§Ø®Øª Tooltip Ø³ÙØ§Ø±Ø´ÛŒ
 */
function createCustomTooltip(context) {
    const tooltip = context.tooltip;
    
    if (tooltip.opacity === 0) {
        return;
    }
    
    // Ø³Ø§Ø®Øª ÛŒØ§ Ø¨Ø±ÙˆØ²Ø±Ø³Ø§Ù†ÛŒ tooltip element
    let tooltipEl = document.getElementById('chartjs-tooltip');
    
    if (!tooltipEl) {
        tooltipEl = document.createElement('div');
        tooltipEl.id = 'chartjs-tooltip';
        tooltipEl.className = 'fixed z-[100] pointer-events-none';
        tooltipEl.innerHTML = '<div class="bg-slate-900/95 backdrop-blur-sm border border-purple-500/30 rounded-lg p-3 shadow-2xl"></div>';
        document.body.appendChild(tooltipEl);
    }
    
    // ØªÙ†Ø¸ÛŒÙ… Ù…ÙˆÙ‚Ø¹ÛŒØª
    const { chart } = context;
    const position = chart.canvas.getBoundingClientRect();
    
    tooltipEl.style.left = position.left + window.pageXOffset + tooltip.caretX + 'px';
    tooltipEl.style.top = position.top + window.pageYOffset + tooltip.caretY + 'px';
    tooltipEl.style.transform = 'translate(-50%, -100%)';
    
    // ØªÙ†Ø¸ÛŒÙ… Ù…Ø­ØªÙˆØ§
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
 * Ø§ÙØ²ÙˆØ¯Ù† Plugin Ø¨Ø±Ø§ÛŒ Ù†Ù…Ø§ÛŒØ´ Ù…Ù‚Ø¯Ø§Ø± Ø¯Ø± Ù†Ù…ÙˆØ¯Ø§Ø±
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

// Ø«Ø¨Øª Plugin
Chart.register(dataLabelsPlugin);

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 5. Ù†Ù…ÙˆØ¯Ø§Ø±Ù‡Ø§ÛŒ Ø¢Ù…Ø§Ø¯Ù‡
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

/**
 * Ø³Ø§Ø®Øª Ù†Ù…ÙˆØ¯Ø§Ø± Ø±Ø´Ø¯ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†
 */
function createUserGrowthChart(canvasId, data) {
    return chartManager.createLineChart(canvasId, {
        labels: data.labels,
        datasets: [{
            label: 'Ú©Ø§Ø±Ø¨Ø±Ø§Ù† Ø¬Ø¯ÛŒØ¯',
            data: data.data,
            borderColor: CHART_COLORS.primary,
            backgroundColor: CHART_COLORS.primaryLight,
            fill: true
        }]
    }, {
        plugins: {
            title: {
                display: true,
                text: 'Ø±Ø´Ø¯ Ú©Ø§Ø±Ø¨Ø±Ø§Ù†',
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
 * Ø³Ø§Ø®Øª Ù†Ù…ÙˆØ¯Ø§Ø± Ø¯Ø±Ø¢Ù…Ø¯
 */
function createRevenueChart(canvasId, data) {
    return chartManager.createMixedChart(canvasId, {
        labels: data.labels,
        datasets: [
            {
                type: 'bar',
                label: 'Ù…Ø¨Ù„Øº Ø¯ÙˆÙ†ÛŒØª (ØªÙˆÙ…Ø§Ù†)',
                data: data.amounts,
                backgroundColor: CHART_COLORS.successLight,
                borderColor: CHART_COLORS.success,
                yAxisID: 'y'
            },
            {
                type: 'line',
                label: 'ØªØ¹Ø¯Ø§Ø¯ Ø¯ÙˆÙ†ÛŒØª',
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
 * Ø³Ø§Ø®Øª Ù†Ù…ÙˆØ¯Ø§Ø± ÙˆØ¶Ø¹ÛŒØª Ú©Ø§Ø±Ø¨Ø±Ø§Ù†
 */
function createUserStatusChart(canvasId, data) {
    return chartManager.createDoughnutChart(canvasId, {
        labels: ['VIP', 'ÙØ¹Ø§Ù„', 'ØºÛŒØ±ÙØ¹Ø§Ù„', 'Ø¨Ù„Ø§Ú© Ø´Ø¯Ù‡'],
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
 * Ø³Ø§Ø®Øª Ù†Ù…ÙˆØ¯Ø§Ø± ÙØ¹Ø§Ù„ÛŒØª Ø³Ø§Ø¹ØªÛŒ
 */
function createHourlyActivityChart(canvasId, data) {
    return chartManager.createBarChart(canvasId, {
        labels: data.labels,
        datasets: [{
            label: 'ØªØ¹Ø¯Ø§Ø¯ Ù¾ÛŒØ§Ù…',
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

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 6. Ø±Ø§Ù‡â€ŒØ§Ù†Ø¯Ø§Ø²ÛŒ Ø§ÙˆÙ„ÛŒÙ‡
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

// Ø³Ø§Ø®Øª ChartManager instance
const chartManager = new ChartManager();

// ØªÙ†Ø¸ÛŒÙ… Chart.js defaults
Chart.defaults.font.family = 'Vazirmatn';
Chart.defaults.color = 'rgba(255, 255, 255, 0.6)';
Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.05)';

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// 7. Export
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

window.ChartManager = ChartManager;
window.chartManager = chartManager;
window.CHART_COLORS = CHART_COLORS;
window.CHART_PALETTE = CHART_PALETTE;
window.CHART_DEFAULTS = CHART_DEFAULTS;

// ØªÙˆØ§Ø¨Ø¹ Ú©Ù…Ú©ÛŒ
window.formatChartData = formatChartData;
window.parseChartData = parseChartData;
window.updateChartFromAPI = updateChartFromAPI;
window.createCustomTooltip = createCustomTooltip;

// Ù†Ù…ÙˆØ¯Ø§Ø±Ù‡Ø§ÛŒ Ø¢Ù…Ø§Ø¯Ù‡
window.createUserGrowthChart = createUserGrowthChart;
window.createRevenueChart = createRevenueChart;
window.createUserStatusChart = createUserStatusChart;
window.createHourlyActivityChart = createHourlyActivityChart;

// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
// Ù¾Ø§ÛŒØ§Ù† ÙØ§ÛŒÙ„
// â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•