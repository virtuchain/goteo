<?php

use Goteo\Core\View,
    Goteo\Model\User,
    Goteo\Model\Image,
    Goteo\Model\Project\Cost,
    Goteo\Model\Project\Support,
    Goteo\Model\Project\Category,
    Goteo\Model\Blog,
    Goteo\Library\Text;

$project = $this['project'];
$show    = $this['show'];
$step    = $this['step'];
$post    = $this['post'];
$blog    = $this['blog'];

$user    = $_SESSION['user'];
$personalData = ($user instanceof User) ? User::getPersonal($user->id) : new stdClass();

if (!empty($project->num_investors)) {
    $supporters = ' (' . $project->num_investors . ')';
} else {
    $supporters = '';
}
if (!empty($project->num_messengers)) {
    $messages = ' (' . $project->num_messengers . ')';
} else {
    $messages = '';
}
if (!empty($project->num_posts)) {
    $updates = ' (' . $project->num_posts . ')';
} else {
    $updates = '';
}

$URL = \SITE_URL;

$bodyClass = 'project-show';

// metas og: para que al compartir en facebook coja bien el nombre y la imagen (todas las de proyecto y las novedades
$ogmeta = array(
    'title' => htmlspecialchars($project->name, ENT_QUOTES),
    'description' => htmlspecialchars($project->subtitle, ENT_QUOTES)."\n".Text::get('regular-by').' '.$project->user->name,
    'url' => $URL . '/project/'.$project->id
);

if ($show == 'updates') {
    $ogmeta['url'] .= '/updates';
    if (!empty($post)) {
        $ogmeta['url'] .= '/'.$post;
        $tpost = $blog->posts[$post];
        $ogmeta['title'] .= (empty($tpost->title)) ?: ' - '.$tpost->title;
    }
}

// todas las imagenes del proyecto
if (is_array($project->gallery)) {
    foreach ($project->gallery as $pgimg) {
        if ($pgimg instanceof Image) {
            $ogmeta['image'][] = $pgimg->getLink(580, 580);
        }
    }
}

foreach ($blog->posts as $bpost) {
    if (is_array($bpost->gallery)) {
        foreach ($bpost->gallery as $bpimg) {
            if ($bpimg instanceof Image) {
                $ogmeta['image'][] = $bpimg->getLink(500, 285);
            }
        }
    }
}


include 'view/prologue.html.php' ?>

<?php include 'view/header.html.php' ?>

        <div id="sub-header">
            <div class="project-header">
                <a href="/user/<?php echo $project->owner; ?>"><img src="<?php echo $project->user->avatar->getLink(56,56, true) ?>" /></a>
                <h2><span><?php echo htmlspecialchars($project->name) ?></span></h2>
                <div class="project-subtitle"><?php echo htmlspecialchars($project->subtitle) ?></div>
                <div class="project-by"><a href="/user/<?php echo $project->owner; ?>"><?php echo Text::get('regular-by') ?> <?php echo $project->user->name; ?></a></div>
                <br/>

                <?php if (!empty($project->cat_names)) : ?>
                <div class="categories"><h3><?php echo Text::get('project-view-categories-title'); ?></h3>
                    <?php $sep = ''; foreach ($project->cat_names as $key=>$value) :
                        echo $sep.'<a href="/discover/results/'.$key.'/'.$value.'">'.htmlspecialchars($value).'</a>';
                    $sep = ', '; endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($project->node) && $project->node != \NODE_ID) : ?>
                <div class="nodemark"><a class="node-jump" href="<?php echo $project->nodeData->url ?>" ><img src ="/nodesys/<?php echo $project->node ?>/sello.png" alt="<?php echo htmlspecialchars($project->nodeData->name) ?>" title="Nodo <?php echo htmlspecialchars($project->nodeData->name) ?>"/></a></div>
                <?php endif; ?>
            </div>

            <div class="sub-menu">
                <?php echo new View('view/project/view/menu.html.php',
                            array(
                                'project' => $project,
                                'show' => $show,
                                'supporters' => $supporters,
                                'messages' => $messages,
                                'updates' => $updates
                            )
                    );
                ?>
            </div>

        </div>

<?php if(isset($_SESSION['messages'])) { include 'view/header/message.html.php'; } ?>


        <div id="main" class="threecols">

            <div class="side">
            <?php
            // el lateral es diferente segun el show (y el invest)
            echo
                new View('view/project/widget/support.html.php', array('project' => $project));

            // seleccionado para capital riego
            if ($project->called) {
                echo new View('view/project/widget/called.html.php', array('project' => $project));
            }

            if ((!empty($project->investors) &&
                !empty($step) &&
                in_array($step, array('start', 'login', 'confirm', 'continue', 'ok', 'fail')) )
                || $show == 'messages' ) {
                echo new View('view/project/widget/investors.html.php', array('project' => $project));
            }

            if ($project->status == 5 && $show != 'rewards' && $show != 'messages') {
                echo new View('view/project/widget/rewards.html.php', array('project' => $project, 'only'=>'social'));
            }
            
            if (!empty($project->supports)) {
                echo new View('view/project/widget/collaborations.html.php', array('project' => $project));
            }

            if ($show != 'rewards' && $show != 'messages') {
                $only_rew = ($project->status == 5) ? 'individual' : null;
                echo new View('view/project/widget/rewards.html.php', array('project' => $project, 'only'=>$only_rew));
            }

            echo new View('view/user/widget/user.html.php', array('user' => $project->user));

            ?>
            </div>

            <?php $printSendMsg = false; ?>
            <div class="center">
			<?php
                // los modulos centrales son diferentes segun el show
                switch ($show) {
                    case 'needs':
                        if ($this['non-economic']) {
                            echo
                                new View('view/project/widget/non-needs.html.php',
                                    array('project' => $project, 'types' => Support::types()));
                        } else {
                        echo
                            new View('view/project/widget/needs.html.php', array('project' => $project, 'types' => Cost::types())),
                            new View('view/project/widget/schedule.html.php', array('project' => $project)),
                            new View('view/project/widget/sendMsg.html.php', array('project' => $project));
                        }
                        break;
						
                    case 'supporters':

						// segun el paso de aporte
                        if (!empty($step) && in_array($step, array('start', 'login', 'confirm', 'continue', 'ok', 'fail'))) {

                            switch ($step) {
                                case 'continue':
                                    echo
                                        new View('view/project/widget/investMsg.html.php', array('message' => $step, 'user' => $user)),
                                        new View('view/project/widget/invest_redirect.html.php', array('project' => $project, 'personal' => $personalData, 'step' => $step, 'allowpp'=> $this['allowpp']));
                                    break;
									
                                case 'ok':
                                    echo
                                        new View('view/project/widget/investMsg.html.php', array('message' => $step, 'user' => $user)), new View('view/project/widget/spread.html.php',array('project' => $project));
										//sacarlo de div#center
										$printSendMsg=true;										
                                    break;
									
                                case 'fail':
                                    echo
                                        new View('view/project/widget/investMsg.html.php', array('message' => $step, 'user' => $user)),
                                        new View('view/project/widget/invest.html.php', array('project' => $project, 'personal' => $personalData, 'allowpp'=> $this['allowpp']));
                                    break;
                                default:
                                    echo
                                        new View('view/project/widget/investMsg.html.php', array('message' => $step, 'user' => $user)),
                                        new View('view/project/widget/invest.html.php', array('project' => $project, 'personal' => $personalData, 'step' => $step, 'allowpp'=> $this['allowpp']));
                                    break;
                            }
                        } else {
                            echo
                                new View('view/project/widget/supporters.html.php', $this),
                                new View('view/worth/legend.html.php');
                        }
                        break;
						
                    case 'messages':
                        echo
                            new View('view/project/widget/messages.html.php', array('project' => $project));
                        break;
                   
				    case 'rewards':
                        if ($project->status == 5) {
                            echo new View('view/project/widget/rewards-summary.html.php', array('project' => $project, 'only'=>'social'));
                            echo new View('view/project/widget/rewards-summary.html.php', array('project' => $project, 'only'=>'individual'));
                        } else {
                            echo new View('view/project/widget/rewards-summary.html.php', array('project' => $project));
                        }
                        break;
                    
					case 'updates':
                        echo
                            new View('view/project/widget/updates.html.php', array('project' => $project, 'blog' => $blog, 'post' => $post));
                        break;
                    
					case 'home':
					
                    default:
                        if (!empty($project->media->url)) {
                            echo new View('view/project/widget/media.html.php', array('project' => $project));
                        }

                        echo new View('view/project/widget/langs.html.php', array('project' => $project));

                        echo new View('view/project/widget/share.html.php', array('project' => $project));
                        if (!empty($project->patrons)) {
                            echo new View('view/project/widget/patrons.html.php', array('patrons' => $project->patrons));
                        }
                        echo new View('view/project/widget/summary.html.php', array('project' => $project));

                        // wall of friends, condicional
                        if (round(($project->invested / $project->mincost) * 100) > 20) {
                            echo new View('view/project/widget/wof.html.php', array('project' => $project));
                        }

                        break;
                }
                ?>
             </div>

			<?php
				if($printSendMsg){
					 echo new View('view/project/widget/sendMsg.html.php',array('project' => $project));
				}
            ?>

        </div>

        <?php include 'view/footer.html.php' ?>
		<?php include 'view/epilogue.html.php' ?>