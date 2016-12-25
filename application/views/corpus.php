<section class="bg-yellow"><!-- шапка -->
	<div class="wrap">
		<div class="row">
		    <div class="col col-2">
			    <div class="date">
			    	на <?php echo $viewedTime; ?>
			    </div>
		    </div>
			<div class="col col-10">

				<h1>
				<a href="<?php echo base_url('/digest/toBegin/');?>">
				Дайджест новостей в Иванове</a>
				</h1>

			</div>
		</div>
	</div><!--/wrap-->
</section><!-- /шапка -->
<section><!-- погода -->
	<div class="wrap height-indent">
		<div class="row">
			<div class="col col-5">
			<button data-component="toggleme" data-target="#weather-text"
			 data-text="Скрыть прогноз" class="button outline small">
				Прогноз погоды
			</button>
			</div>
			<div class="col col-1">&nbsp;</div>
			<div class="col col-6">
			<div class="ya-share2  float-right"
				data-services="vkontakte,odnoklassniki,facebook,twitter,collections,gplus,moimir">
			</div>
			</div>
		</div>
		<div id="weather-text" class="hide height-indent">
			<div class="row" >
				<?php for ($i=0; $i <count($mi_title) ; $i++): ?>
					<div class="col col-6"><!-- блок погоды -->
						<em><?php echo $mi_title[$i]; ?></em><br>
						<snap><?php echo $mi_description[$i]; ?></snap>
					</div><!-- /блок погоды -->
				<?php endfor; ?>
			</div>
			<div class="centered text-center">
				<a href="http://meteoinfo.ru/"><em>Источник</em></a> &rarr;
			</div>
		</div>

	</div>
</section><!-- /погода -->

<section><!-- табы -->
	<div class="wrap height-indent">
		<nav class="tabs" data-component="tabs">
		    <ul>
		        <?php
				$i = 1;
		        foreach ($namePubs as $nP): ?>
		        <li><a href="#tab<?php echo $i;?>"><?php echo $nP;?></a></li>
		        <?php
				$i++;
		        endforeach; ?>
		    </ul>
		</nav>
	</div>
</section><!-- /табы -->

<div class="wrap">
	<?php foreach($idsPubs as $idPub): ?>
	<!-- содержание таба -->
	<div id="tab<?php echo $idPub;?>"> <!-- <div id="tab№"> -->
		<div class="row">
			<?php // выводим в обратном порядке
				for ($i=count($src_link)-1; $i >=0 ; $i--):?>
			<!-- анонс -->
			<div class="col col-4">
			<h2><?php echo $ad_title[$idPub][$i]; ?></h2>
			<p><?php echo $ad_text[$idPub][$i]; ?></p>
			<a href="<?php echo $src_link[$idPub][$i]; ?>"><em>К новости</em></a> &rarr;
			</div>
			<!-- /анонс -->
			<?php endfor; ?>
		</div>
	</div>
	<!-- /содержание таба -->
	<?php endforeach; ?>
</div>

<section><!-- пагинация -->
	<nav class="pagination pager centered">
	    <ul>
	        <li class="prev"><a href="<?php echo $pagination[0]; ?>">Ранее</a></li>
	        <li class="next"><a href="<?php echo $pagination[1]; ?>">Позднее</a></li>
	    </ul>
	</nav>
</section><!-- /пагинация -->