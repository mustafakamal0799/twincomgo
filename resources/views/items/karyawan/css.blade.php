<style>
/* Detail Header Styles */
.detail-header {
    background: white;
    padding: 1.5rem 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-bottom: 2rem;
    border-left: 4px solid #3498db;
}

.detail-header h3 {
    color: #2c3e50;
    margin: 0;
    font-size: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.detail-header h3 i {
    color: #3498db;
    font-size: 1.6rem;
}

.detail-header .btn {
    border-radius: 8px;
    padding: 0.5rem 1.25rem;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.detail-header .btn-light {
    background: #f8f9fa;
    border-color: #e9ecef;
    color: #495057;
}

.detail-header .btn-light:hover {
    background: #e9ecef;
    border-color: #dee2e6;
    transform: translateY(-1px);
}

.detail-header .btn-danger {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    border: none;
    box-shadow: 0 2px 4px rgba(231, 76, 60, 0.3);
}

.detail-header .btn-danger:hover {
    background: linear-gradient(135deg, #c0392b, #a93226);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(231, 76, 60, 0.4);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .detail-header {
        padding: 1.25rem 1.5rem;
        text-align: center;
    }
    
    .detail-header h3 {
        font-size: 1.3rem;
        justify-content: center;
        margin-bottom: 1rem;
    }
    
    .detail-header .btn {
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
    }
}

.price-card {
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    padding: 1.5rem;
}
.price-user { font-size: 1.7rem; color:#198754; font-weight:700; }
.price-reseller { font-size: 1.7rem; color:#0d6efd; font-weight:700; }

/* Warehouse Dashboard Styles */
.warehouse-section {
    padding: 1.5rem 0;
}

.warehouse-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    margin-bottom: 1.5rem;
    overflow: hidden;
    background: white;
}

.warehouse-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.warehouse-card .card-header {
    background: linear-gradient(135deg, #2c3e50, #4a6572);
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 1rem 1.25rem;
    border-bottom: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* .warehouse-card .card-header::before {
    content: "📦";
    font-size: 1.2rem;
} */


/* store */
.warehouse-card.warehouse-store .card-header::before {
    content: "🏬";
}

/* tsc */
.warehouse-card.warehouse-tsc .card-header::before {
    content: "🏢";
}

/* reseller */
.warehouse-card.warehouse-reseller .card-header::before {
    content: "🤝";
}

/* panda */
.warehouse-card.warehouse-panda .card-header::before {
    content: "🐼";
}

.warehouse-card .card-body {
    padding: 0;
}

.warehouse-card .table {
    margin-bottom: 0;
    font-size: 0.9rem;
}

.warehouse-card .table thead {
    background-color: #f8f9fa;
}

.warehouse-card .table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.warehouse-card .table td {
    padding: 0.75rem 1rem;
    vertical-align: middle;
    border-color: #f1f3f4;
}

.warehouse-card .table tbody tr:hover {
    background-color: rgba(52, 152, 219, 0.05);
}

.warehouse-card .table tbody tr:last-child td {
    border-bottom: 1px solid #dee2e6;
}

.total-badge {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
    padding: 0.4rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.95rem;
    box-shadow: 0 2px 4px rgba(39, 174, 96, 0.2);
}

.warehouse-card .bg-light {
    background-color: #f8f9fa !important;
    border-top: 1px solid #e9ecef !important;
    padding: 1rem 1.25rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .warehouse-card .card-header {
        font-size: 1rem;
        padding: 0.875rem 1rem;
    }
    
    .warehouse-card .table th,
    .warehouse-card .table td {
        padding: 0.5rem 0.75rem;
    }
    
    .total-badge {
        padding: 0.3rem 0.8rem;
        font-size: 0.9rem;
    }
}

/* Custom colors for different warehouse types */
#warehouse_store .card-header {
    background: linear-gradient(135deg, #0b7710, #0aa10a);
}

#warehouse_tsc .card-header {
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
}

#warehouse_reseller .card-header {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
}

#warehouse_konsinyasi .card-header {
    background: linear-gradient(135deg, #f39c12, #d35400);
}

#warehouse_panda .card-header {
    background: linear-gradient(135deg, #bc1a1a, #a01616);
}

/* Animation for card appearance */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.warehouse-card {
    animation: fadeInUp 0.5s ease forwards;
}

/* Stagger animation for multiple cards */
.warehouse-card:nth-child(1) { animation-delay: 0.1s; }
.warehouse-card:nth-child(2) { animation-delay: 0.2s; }
.warehouse-card:nth-child(3) { animation-delay: 0.3s; }
.warehouse-card:nth-child(4) { animation-delay: 0.4s; }
.warehouse-card:nth-child(5) { animation-delay: 0.5s; }

/* harga box */
/* Price Box Styles */
.price-box {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
}

.price-box:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

#catatanCard:hover{
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

#catatanCard {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    overflow: hidden;
}

#catatanCard::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(to bottom, #2878a7, #20c997);
}

.price-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
}

.price-box .title {
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
}

.price-box .title i {
    font-size: 1.1rem;
}

.price-box h3 {
    font-weight: 700;
    margin: 0.5rem 0;
    font-size: 1.5rem;
    word-break: break-word;
    overflow-wrap: break-word;
}

.price-user {
    color: #28a745;
}

.price-reseller {
    color: #007bff;
}

.price-box hr {
    margin: 1rem 0;
    opacity: 0.3;
}

.price-box p {
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.price-box .fw-semibold {
    color: #2c3e50;
    font-size: 0.95rem;
}

/* Specific styles for each price box */
.col-md-6:first-child .price-box::before {
    background: linear-gradient(to bottom, #28a745, #20c997);
}

.col-md-6:last-child .price-box::before {
    background: linear-gradient(to bottom, #007bff, #6f42c1);
}

/* Responsive fixes for mobile */
@media (max-width: 768px) {
    .price-box {
        margin-bottom: 1rem;
        padding: 1.25rem !important;
    }
    
    .price-box h3 {
        font-size: 1.3rem;
        line-height: 1.3;
    }
    
    .row.g-3 {
        margin-left: -0.5rem;
        margin-right: -0.5rem;
    }
    
    .row.g-3 > [class*="col-"] {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }
}

/* Extra small devices */
@media (max-width: 576px) {
    .price-box h3 {
        font-size: 1.2rem;
    }
    
    .price-box .title {
        font-size: 0.95rem;
    }
    
    .price-box p {
        font-size: 0.85rem;
    }
}

/* Prevent text overflow */
.price-box h3,
.price-box p {
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
}
</style>