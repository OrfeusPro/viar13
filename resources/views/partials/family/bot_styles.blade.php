<style>
    .components-pictures .components-content img {
        display: none;
    }

    @media screen and (min-width: 768px) {
        .components-pictures .components-content img {
            top: 50%;
            left: 45%;
            width: 58%;
            z-index: 1;
            display: block;
            position: absolute;
            -webkit-transform: translate(-50%, -50%);
            transform: translate(-50%, -50%);
        }
    }

    @media screen and (min-width: 1024px) {
        .components-pictures .components-content img {
            top: 47%;
            left: 45%;
            width: 72%;
            z-index: -1;
        }
    }

    @media screen and (min-width: 1400px) {
        .components-pictures .components-content img {
            top: 45%;
            left: 43%;
            width: 77%;
        }
    }

    @media screen and (min-width: 1700px) {
        .components-pictures .components-content img {
            top: 53.1%;
            left: 54.6%;
            width: 686px;
        }
    }



    h2.js_page_name {
        position: relative;
        z-index: 2;
    }

    .generate .generate-content .title:after {
        content: '';
        top: 50%;
        left: 50%;
        z-index: 0;
        width: 300px;
        height: 50px;
        position: absolute;
        background: url(/img/generate-title.png) no-repeat 50% 50%;
        background-size: cover;
        -webkit-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
    }

    @media screen and (min-width: 768px) {
        .generate .generate-content .title::after {
            width: 400px;
            height: 68px;
        }
    }

    @media screen and (min-width: 1280px) {
        .generate .generate-content .title::after {
            width: 636px;
            height: 109px;
        }
    }

    .generate .generate-content .title {
        position: relative;
    }

    .size-item input {
        width: 85px;
        height: 25px;
        padding: 0 5px;
        color: #313131;
        font-size: 15px;
        font-weight: 500;
        line-height: 20px;
        text-align: center;
        border-radius: 5px;
        border: 1px solid #8c535b;
        background-color: #ffffff;
    }

    .tab2-item-child {
        width: 100%;
        height: 400px;
    }

    .range__box {
        display: none;
    }

    @media screen and (min-width: 1400px) {
        .generate .generate-content .gallery-photo .photo-content .slider-tabs .tabs-content {
            padding: 40px 20px;
        }
    }

    .int-draggable img {
        max-width: 178px !important;
        max-height: 356px !important;
    }

    .product-download {
        max-height: 192px;
    }
</style>