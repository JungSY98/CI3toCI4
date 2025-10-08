<!-- ===== 모바일 영역 ===== -->
<div class="divMobile d-block d-xl-none">
	<header class="fixed-top">
		<nav class="navbar navbar-expand-xxl navbar-main bg-faded max1920" id="navSite">
			<div class="container-fluid max1920">
				<a class="navbar-brand" href="/">DONGDAEMUN <span>SUPERPASS</span></a>
				<ul class="navbar-nav w-100">
					<li>
						<div id="searchBar2" class="input-group w-100 pt-2">
							<div class="input-group searchBarWrap">
								<input id="searchInput" type="text" class="form-control form-control-lg border-0" placeholder="<?= $is_domestic ? '장소, 쿠폰 검색' : 'Place, Coupon Search' ?>">
								<button id="btnSearch"><i class="fa fa-search" aria-hidden="true"></i></button>
							</div>
						</div>
					</li>
<!--
			<li class="btnTopInsta"><a href="https://www.instagram.com/ddp_seoul/" target="_blank"><i class="fa fa-instagram"></i></a></li>
						<li class="btnTopFoundation"><a href="https://www.seouldesign.or.kr" target="_blank"><img src="/assets/images/logoFoundation.png" class="topLogo2" alt="서울디자인재단"></a></li>
-->
				</ul>
			</div>
		</nav>
	</header>

	<!-- Mobile Bottom Nav (모바일 전용) -->
	<nav id="mbNav" class="mobile-bottom-nav d-xl-none" aria-label="Mobile bottom menu">
		<button type="button" class="mb-nav-item active" data-action="map" aria-current="page">
			<img src="/assets/images/mobileMenu1.png" alt="Map">
			<span class="d-none">Map</span>
		</button>
		<button type="button" class="mb-nav-item" data-action="stamp">
			<img src="/assets/images/mobileMenu2.png" alt="Stamp">
			<span class="d-none">Stamp</span>
		</button>
		<button type="button" class="mb-nav-item" data-action="store">
			<img src="/assets/images/mobileMenu3.png" alt="Stores">
			<span class="d-none">SuperPass</span>
		</button>
	</nav>

</div>

<!-- PC 전용 좌측 오프캔버스 -->
<div class="divPC d-none d-xl-block">
	<div class="offcanvas offcanvas-left show" id="pcMenuStore" data-bs-backdrop="false" data-bs-scroll="true" tabindex="-1" aria-labelledby="pcMenuStoreLabel">
		<div class="offcanvas-header flex-column align-items-start">
			<a class="navbar-brand" href="/">DONGDAEMUN <span>SUPERPASS</span></a>

			<!-- 검색 -->
			<div class="searchWrapPC w-100">
				<div class="input-group searchBarWrap">
					<input id="pcSearchInput" type="text" class="form-control form-control-lg" placeholder="<?=$is_domestic ? '장소, 쿠폰 검색' : 'Place, Coupon Search' ?>">
					<button id="pcBtnSearch"><i class="fa fa-search" aria-hidden="true"></i></button>
				</div>
				<!-- PC Nav -->
				<nav id="pcNav" class="pc-nav" aria-label="PC menu">
					<button type="button" class="pc-nav-item active" data-action="map" aria-current="page">
						<img src="/assets/images/mobileMenu1.png" alt="Map">
						<span class="d-none">Map</span>
					</button>
					<button type="button" class="pc-nav-item" data-action="stamp">
						<img src="/assets/images/mobileMenu2.png" alt="Stamp">
						<span class="d-none">Stamp</span>
					</button>
					<button type="button" class="pc-nav-item" data-action="store">
						<img src="/assets/images/mobileMenu3.png" alt="Stores">
						<span class="d-none">SuperPass</span>
					</button>
				</nav>
			</div>
		</div>
		<div class="offcanvas-body p-0">
			<!-- 카테고리(줄바꿈) -->
			<div class="pcCatSection bg-white sticky-top">
				<div class="pcCatTitle">EXPLORING THE PERIPHERAL</div>
				<div id="pcCatGrid" class="pcCatGrid" aria-label="categories grid">
				<!-- JS로 동적 채움 -->
				</div>
			</div>

			<div id="pcListToolbar" class="place-toolbar d-xl-flex">
				<div class="ttl">PLACE <small id="pcPlaceCount">0</small></div>
				<div class="place-sort d-none" role="tablist" aria-label="Sort">
					<button id="btnSortDistance" class="active" type="button">지도중심</button>
					<span class="sep">|</span>
					<button id="btnSortRelevance" type="button">관련도순</button>
				</div>
			</div>
			
			<!-- 검색 결과 / 기본 리스트 -->
			<div class="listWrapPC">
				<ul id="pcResultList" class="list-group list-group-flush"></ul>
			</div>
		</div>
	</div>
</div>



<!-- Splash -->
<div id="splashOverlay" aria-hidden="true">
	<div class="splash-inner">
		<img class="splash-img landscape" src="/assets/images/superpass_splash_pc.jpg" alt="Splash PC">
		<img class="splash-img portrait" src="/assets/images/superpass_splash_mobile.jpg" alt="Splash Mobile">
	</div>
</div>

<!-- Map -->
<div id="mapWrap" class="w-100">
	<div id="map"></div>
	<div id="moCatBar" class="d-xl-none">
		<div id="moCatRow" aria-label="categories scroller">
			<!-- JS로 동적 채움 -->
		</div>
	</div>
</div>

<!-- Search Result Panel -->
<div id="resultPanel" class="position-absolute start-50 translate-middle-x">
	<button class="btn btn-dark w-100 m-1" style="z-index:1040" onclick="$('#resultPanel').addClass('d-none')"><i class="fa fa-times-circle" aria-hidden="true"></i></button>
	<div class="resultListWrap card shadow-lg rounded-3 overflow-hidden">
		<ul id="resultList" class="list-group list-group-flush"></ul>
	</div>
</div>

<!-- PC 전용 상세 패널(우측 오프캔버스) -->
<div class="offcanvas offcanvas-start d-none d-xl-flex" tabindex="-1" id="pcPlaceDetail" aria-labelledby="pcPlaceDetailLabel" style="width:520px;">
	<div class="sheet-grip" aria-hidden="true"></div> <!-- 드래그 핸들 -->
	<div class="offcanvas-header">
		<h5 class="offcanvas-title fw-bold" id="pcDetName"></h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body p-0 d-flex flex-column">
		<!-- 1) 사진 슬라이드 -->
		<div id="pcPhotoCarouselWrap" class="border-bottom">
			<div id="pcPhotoCarousel" class="carousel slide" data-bs-ride="carousel">
				<div class="carousel-inner" id="pcPhotoSlides"></div>
				<button class="carousel-control-prev" type="button" data-bs-target="#pcPhotoCarousel" data-bs-slide="prev">
					<span class="carousel-control-prev-icon"></span>
					<span class="visually-hidden">Prev</span>
				</button>
				<button class="carousel-control-next" type="button" data-bs-target="#pcPhotoCarousel" data-bs-slide="next">
					<span class="carousel-control-next-icon"></span>
					<span class="visually-hidden">Next</span>
				</button>
				</div>
			</div>

			<!-- 2~5) 텍스트 정보 -->
			<div class="p-3 border-bottom">
				<!-- <div class="h5 fw-bold mb-2" id="pcDetName2">-</div> -->
				<div class="text-body mb-3" id="pcDetDesc"></div>

				<div class="mb-2">
					<div class="small text-muted mb-1"><?= $is_domestic?'운영시간':'Hours' ?></div>
					<div id="pcDetHours" class="fw-semibold">-</div>
				</div>

				<div class="mb-2">
					<div class="small text-muted mb-1"><?= $is_domestic?'주소':'Address' ?></div>
					<div id="pcDetAddr" class="fw-semibold">-</div>
				</div>
			</div>

			<!-- 6) 쿠폰 리스트 -->
			<div class="p-3">
				<div class="fw-bold mb-2"><?= $is_domestic?'쿠폰 리스트':'Coupons' ?></div>

				<div id="pcCouponCarousel" class="carousel slide" data-bs-touch="true" data-bs-interval="0">
					<div class="carousel-inner" id="pcCouponSlides"><!-- JS로 슬라이드 채움 --></div>
					<div class="carousel-indicators" id="pcCouponDots"><!-- JS로 dots 채움 --></div>

					<button class="carousel-control-prev" type="button" data-bs-target="#pcCouponCarousel" data-bs-slide="prev" style="filter:drop-shadow(0 0 2px #0003);">
						<span class="carousel-control-prev-icon"></span><span class="visually-hidden">Prev</span>
					</button>
					<button class="carousel-control-next" type="button" data-bs-target="#pcCouponCarousel" data-bs-slide="next" style="filter:drop-shadow(0 0 2px #0003);">
						<span class="carousel-control-next-icon"></span><span class="visually-hidden">Next</span>
					</button>
					</div>
				</div>
			</div>
		</div>

	</div>
</div>


<!-- PC 전용: Stamp Tour 패널(우측 오프캔버스) -->
<div class="offcanvas offcanvas-start d-none d-xl-flex" tabindex="-1" id="pcStampDetail" aria-labelledby="pcStampDetailLabel" style="width:520px;">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title fw-bold" id="pcStampDetailLabel">Stamp Tour Event</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body p-0 d-flex flex-column">
		<!-- JS가 모바일 #stampSheet 본문을 그대로 채움 -->
		<div id="pcStampBody" class="p-0" style="height:100%; overflow:hidden;overflow-y:auto;">
<?=$stampExplain?>
		</div>
	</div>
</div>

<!-- Mobile: 검색 결과 시트 (하단 반오픈 + 드래그 업) -->
<div class="offcanvas offcanvas-bottom d-xl-none" tabindex="-1" id="mSearchSheet" aria-labelledby="mSearchSheetLabel" style="height:50vh;">
	<div class="sheet-grip" aria-hidden="true"></div>
	<div class="offcanvas-header pt-2 pb-1">
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body p-0">
		<ul id="mSearchList" class="list-group list-group-flush"></ul>
	</div>
</div>

<!-- Mobile - Bottom Detail Sheet (50%) -->
<div class="offcanvas offcanvas-bottom d-xl-none" tabindex="-1" id="mPlaceSheet" aria-labelledby="mPlaceSheetLabel" style="height:60vh;">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title fw-bold" id="mDetName">-</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
	</div>
	<div class="offcanvas-body p-0 d-flex flex-column">
		<!-- 1) 사진 -->
		<div id="mPhotoCarouselWrap" class="border-bottom">
			<div id="mPhotoCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="0">
				<div class="carousel-inner" id="mPhotoSlides"></div>
				<button class="carousel-control-prev" type="button" data-bs-target="#mPhotoCarousel" data-bs-slide="prev">
					<span class="carousel-control-prev-icon"></span><span class="visually-hidden">Prev</span>
				</button>
				<button class="carousel-control-next" type="button" data-bs-target="#mPhotoCarousel" data-bs-slide="next">
					<span class="carousel-control-next-icon"></span><span class="visually-hidden">Next</span>
				</button>
			</div>
		</div>
		<div class="p-3">
			<div class="fw-bold mb-2">Coupons</div>
			<div id="mCouponCarousel" class="carousel slide" data-bs-touch="true" data-bs-interval="0">
				<div class="carousel-inner" id="mCouponSlides"></div>
				<div class="carousel-indicators" id="mCouponDots"></div>
				<button class="carousel-control-prev" type="button" data-bs-target="#mCouponCarousel" data-bs-slide="prev">
					<span class="carousel-control-prev-icon"></span><span class="visually-hidden">Prev</span>
				</button>
				<button class="carousel-control-next" type="button" data-bs-target="#mCouponCarousel" data-bs-slide="next">
					<span class="carousel-control-next-icon"></span><span class="visually-hidden">Next</span>
				</button>
			</div>
		</div>
		<!-- 2~5) 텍스트 -->
		<div class="p-3 border-bottom">
			<div class="text-body mb-3" id="mDetDesc" style="white-space:pre-line;"></div>

			<div class="mb-2">
				<div class="small text-muted mb-1"><?= $is_domestic?'운영시간':'Hours' ?></div>
				<div id="mDetHours" class="fw-semibold">-</div>
			</div>
			<div class="mb-2">
				<div class="small text-muted mb-1"><?= $is_domestic?'주소':'Address' ?></div>
				<div id="mDetAddr" class="fw-semibold">-</div>
			</div>
		</div>
	</div>
</div>

<!-- Mobile: Stamp 이벤트 (반오픈 → 드래그로 확장) -->
<div class="offcanvas offcanvas-bottom d-xl-none" tabindex="-1" id="stampSheet" aria-labelledby="stampSheetLabel">
	<div class="sheet-grip" aria-hidden="true"></div> <!-- 드래그 핸들 -->
	<div class="offcanvas-header pt-2 pb-1">
		<h5 class="offcanvas-title fw-bold p-3" id="stampSheetLabel">Stamp Tour Event</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
	</div>
	<div class="offcanvas-body pt-1 px-2">
<?=$stampExplain?>
	</div>
</div>
<!-- 쿠폰 상세 모달 -->
<div class="modal fade modal-rounded" id="couponModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
		<div class="modal-content">
			<div class="modal-header">
				<input type="hidden" id="modalCouponTitle">
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body pt-0">
				<h3 class="modal-title fw-bold text-center p-3" id="modalStoreName">Store</h3>
				<div class="ticketTop">
					<img src="/assets/images/couponTopLogo.jpg">
					<h4 id="ticketTopInfo"></h4>
				</div>
				
				<div class="ticket">
					<p class="stars">✦ ✦ ✦ ✦</p>

					<div class="kv">
					<div class="label">Desc.</div>
					<div id="modalCouponDesc">-</div>

					<div class="label">Valid until</div>
					<div id="modalBadgeValid">-</div>

					<div class="label">Location</div>
					<div id="couponLocation">-</div>

					<div class="label">Hours</div>
					<div id="couponOpenTime">-</div>
					</div>

					<p class="note">· You can use only 1 coupon per person.</p>
				</div>

				<div class="mt-3 mb-1">
					<div class="input-group couponInputPrice">
						<div class="input-group-text">Price</div>
						<input type="number" class="form-control form-control-lg" id="modalPrice" placeholder="Total Price" pattern="[0-9]*">
						<span class="input-group-text">KRW</span>
					</div>
						</div>
						<div class="mb-2">
					<div class="input-group couponInputPrice">
						<div class="input-group-text">Code</div>
						<input type="text" class="form-control form-control-lg" id="modalCode" placeholder="Coupon Code">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-warning btn-lg w-100 fw-bold p-3" id="btnModalUse">Coupon USE</button>
			</div>
		</div>
	</div>
</div>

<!-- 쿠폰 사용 결과 모달 -->
<div class="modal fade modal-rounded" id="resultModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
		<div class="modal-content" style="position:relative; overflow:visible; ">
			<div class="modal-header">
				<h5 class="modal-title fw-bold" id="resultStoreName">Store</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body text-center" style="position:relative; z-index:0;">
		
				<div class="slot" aria-hidden="true"></div>
					<!-- 티켓 본문 -->
					<article class="receipt" role="region" aria-label="DDP Coupon Result">
						<div class="row">
							<div class="logo">
								<img src="/assets/images/couponTopLogo.png">
							</div>
						</div>

						<h1 id="resultCouponTitle">-</h1>
						<div class="divider"></div>
						<p class="lead">Coupon usage has been completed.</p>
						<div class="divider"></div>
						<p class="lead" id="resultLine2">-</p>
						<div class="paypill" id="resultPay">-</div>
						<div class="divider"></div>
						<p class="thanks">Thank you.</p>
					</article>

					<button class="btn btn-dark btn-lg w-100 p-3 rounded-5 mt-3" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
</div>


<?=$scriptGlobalVal ?>
<?=$scriptUtils ?>
<?=$scriptApiGuard ?>
<?=$scriptSmartLoader ?>
<?=$scriptLists ?>
<?=$scriptMarkers ?>
<?=$scriptMapInit ?>
<?=$scriptCategories ?>
<?=$scriptDetailPC ?>
<?=$scriptStampPC?>
<?=$scriptDetailMobile ?>
<?=$scriptStampSheet?>
<?=$scriptCoupons ?>
<?=$scriptMobileBottomNav ?>
<?=$scriptRelayoutHardUnlock?>

