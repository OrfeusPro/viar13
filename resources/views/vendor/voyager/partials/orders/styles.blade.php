<style>
    .icon-icon22,
    .icon-icon25 {
        position: relative;
    }

    ul.user__comments__list {
        padding: 0;
        margin: 0;
        list-style-type: none;
    }

    .order__list__routes {
        display: flex;
        align-self: center;
    }

    .order__list__routes a+a {
        margin-left: 15px;
    }

    ul.user__comments__list:before {
        content: '';
        display: none;
    }

    i.icon-icon22:before {
        content: '<';
    }

    i.icon-icon25:before {
        content: '>';
    }

    .painter__list {}

    .painter__list img {
        width: 50px;
        height: 50px;
        object-fit: cover
    }

    .painter__img {
        margin-bottom: 10px;
    }

    .commentary__bot__inline {
        list-style-type: none;
        padding: 0;
        margin: 0;
        ;
    }

</style>

<style>
    svg,
    img {
        max-width: 150px !important;
    }

    thead,
    tbody,
    .table.dataTable {
        max-width: 100% !important;
    }

    .list__adm__items {
        padding: 0;
    }

    #dataTable tr>td:last-child {
        position: relative;
    }

    button,
    input,
    select,
    textarea {
        max-width: 100%;
    }


    @media(max-width:1500px) {
        .td__id{
            max-width: 50px;
        }
        td.td-0,
        td.td-1,
        td.td-2,
        td.td-3,
        td.td-4,
        td.td-4,
        td.td-5,
        td.td-6{
            max-width: unset;
            width: unset!important;;
        }
        #dataTable tr:not(.eticet__tr) tbody>tr {
            width: 100%;
            display: grid!important;
            grid-template-columns: repeat(8, 1fr);
            width: 100%;
            overflow-x: auto;
        }
        /* td.td-0{
            min-width: 136px;
        }

        td.td-1{
            min-width: 119px;
        }

        td.td-2{
            min-width: 198px;
        }

        .orda__col{
            max-width: 237px;
        }

        span.text__btn__span {
            font-size: 13px;
        }

        td.td-3{
            min-width: 195px;
        } */

        #dataTable tr:not(.eticet__tr) tr>td:last-child {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            justify-content: flex-start;
        }

        #dataTable tr:not(.eticet__tr) > td:last-child > a.btn,
        #dataTable tr:not(.eticet__tr) > td:last-child > .order__action {
            float: none !important;
            display: table !important;
            width: auto !important;
            min-width: 44px !important;
            max-width: 230px !important;
            margin: 8px auto 0 !important;
            clear: both !important;
            white-space: normal !important;
        }

        #dataTable tr:not(.eticet__tr) > td:last-child > a.btn:first-child,
        #dataTable tr:not(.eticet__tr) > td:last-child > .order__action:first-child {
            margin-top: 0 !important;
        }

        #dataTable tr:not(.eticet__tr) > td:last-child > .btn_orders .text__btn__span,
        #dataTable tr:not(.eticet__tr) > td:last-child > .order__action--client .text__btn__span,
        #dataTable tr:not(.eticet__tr) > td:last-child > .order__action--history .text__btn__span {
            display: inline-block !important;
        }

        /* #dataTable tbody>tr {
            position: relative;
            display: flex;
            justify-content: space-between;
            width: 100%;
        } */

        #dataTable tbody>tr>td {
            position: relative;
        }

        svg,
        img {
            max-width: 120px !important;
        }

        img.img__et {
            max-width: 100% !important;
        }

        .panel-bordered>.panel-body {
            padding: 5px;
        }

        #dataTable thead {
            display: none !important;
        }
    }

    .tac {
        text-align: center;
        display: block;
    }

    .order_comment {
        font-size: 0.9em;
    }

    .payment-request-quick {
        margin-top: 18px;
        padding: 12px;
        border: 1px solid #dcdcdc;
        border-radius: 8px;
        background: #fafafa;
        text-align: left;
    }

    .payment-request-quick__title {
        margin-bottom: 10px;
        font-weight: 600;
        font-size: 14px;
        color: #1e2533;
    }

    .payment-request-quick__create {
        width: 100%;
    }

    .payment-request-quick__item {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #ececec;
        font-size: 12px;
        line-height: 1.4;
    }

    .payment-request-quick__status {
        display: inline-block;
        margin-top: 4px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 600;
    }

    .payment-request-quick__status.pending {
        background: #fff0d4;
        color: #8a5a00;
    }

    .payment-request-quick__status.paid {
        background: #e8f7e8;
        color: #247a24;
    }

    .payment-request-quick__link {
        width: 100%;
        margin: 6px 0;
        font-size: 12px;
    }

    .payment-request-quick__actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .payment-request-quick__actions .btn {
        flex: 1 1 auto;
    }

    .order-column-compact-blocks {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 12px;
    }

    .order-invoice-links {
        margin: 0;
        padding-left: 0;
        list-style: none;
        text-align: center;
    }

    .order-invoice-links li {
        margin-bottom: 8px;
    }

    .order-invoice-links li:last-child {
        margin-bottom: 0;
    }

    .order-invoice-card {
        background: #fff;
        border: 1px solid #e8e8e8;
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
    }

    .payment-request-modal {
        position: fixed !important;
        inset: 0 !important;
        z-index: 1050 !important;
        width: 100vw !important;
        height: 100vh !important;
        min-height: 100vh !important;
        overflow-y: auto !important;
        background: rgba(15, 23, 42, 0.72) !important;
    }

    .payment-request-modal.fade .modal-dialog {
        transform: none !important;
    }

    .payment-request-modal.in {
        position: fixed !important;
        inset: 0 !important;
        z-index: 1050 !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .payment-request-modal .modal-dialog {
        width: 460px !important;
        max-width: calc(100vw - 40px) !important;
        margin: 0 !important;
        position: relative;
        z-index: 2;
    }

    .payment-request-modal .modal-content {
        width: 100%;
        border: 0;
        border-radius: 12px;
        box-shadow: 0 24px 60px rgba(30, 37, 51, 0.22);
    }

    .payment-request-modal .modal-header,
    .payment-request-modal .modal-footer {
        border: 0;
    }

    .payment-request-modal .modal-body {
        padding-top: 0;
    }

    .order-chat-preview {
        margin-top: 10px;
        padding: 10px 12px;
        border: 1px solid #d8e4f3;
        border-radius: 8px;
        background: #f8fbff;
        color: #1e2533;
        font-weight: 600;
        font-size: 13px;
        line-height: 1.45;
        text-align: left;
    }

    .order-chat-preview__item + .order-chat-preview__item {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e5edf7;
    }

    .order-chat-preview__meta {
        font-weight: 700;
        font-size: 12px;
        text-align: left;
    }

    .order-chat-preview__author {
        color: #1e2533;
    }

    .order-chat-preview__text {
        margin-top: 4px;
        word-break: break-word;
        white-space: pre-wrap;
        text-align: left;
        font-weight: 400;
    }

    .modal-backdrop.payment-request-modal-backdrop {
        position: fixed !important;
        inset: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        min-height: 100vh !important;
        background: #0f172a !important;
        z-index: 1040 !important;
    }

    .modal-backdrop.payment-request-modal-backdrop.in {
        opacity: 0.72 !important;
    }

    @media (max-width: 767px) {
        .payment-request-modal.in {
            align-items: flex-start;
            padding: 70px 12px 12px;
        }

        .payment-request-modal .modal-dialog {
            width: 100% !important;
            max-width: calc(100vw - 24px) !important;
        }
    }

    @media (max-width: 767px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        #dataTable tbody > tr:not(.eticet__tr) {
            display: grid !important;
            grid-template-columns: 180px 160px repeat(6, 250px);
            min-width: 1840px;
            width: max-content;
        }

        #dataTable tbody > tr:not(.eticet__tr) > td {
            min-width: 0;
            overflow-wrap: break-word;
        }

        #dataTable tbody > tr:not(.eticet__tr) > td:first-child {
            min-width: 170px;
        }

        .order-column-compact-blocks {
            margin-top: 10px;
        }
    }

</style>
