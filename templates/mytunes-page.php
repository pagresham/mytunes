<?php
/**
 * Template Name: MytunesArchive
 *
 * Created by PhpStorm.
 * User: piercegresham
 * Date: 2/20/18
 * Time: 11:52 PM
 */
get_header();
$ppp = null;
$direction = 'ASC';
if( isset( $_POST['mytunes_submit'] ) ) {
//    print "<p>". print_r($_POST) ."</p>";
    if(isset($_POST['posts_per_page'])) {
        $ppp = $_POST['posts_per_page'];
    }
    if(isset($_POST['direction'])) {
        $direction = $_POST['direction'];
    }
    // i did prove I could pass in variable from a form, and use those vars to modify my post_query //
    // Prvoving that filtering posts is easy

    print_r($_POST);
}






$keys = array('A','Bb','B','C','C#','D','Eb','E','F','F#','G','Ab');
$temp = $wp_query;
//$wp_query = null;

$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

$p = ($ppp) ? $ppp : 5;
$args = array(
    'post_type' => 'mytunes',
    'posts_per_page' => $p,
    'paged' => $paged,
    'orderby' => array(
        'title' => $direction,
    )
);

$query = new WP_Query( $args );

print "<div class='container'>";

?>


    <!--            You want to get an array of all of the avalable metaData keys from the post    -->
    <div class="mytunes-filter-form">
        <form action="#" method="post">
            <h5>MyTunes Filter Form</h5>

                <div class="form-group row">
                    <label class="h5 col-sm-2" for="">Key: </label>
                    <select class="form-control col-sm-2" name="mytunes-filter-key" id="">
                        <?php
                        foreach ($keys as $key) {
                            print "<option value='" . $key . "'>$key</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 h5" for="posts_per_page">Posts Per Page</label>
                    <select class="form-control col-sm-2" name="posts_per_page" id="posts_per_page">
                        <option value="-1">All</option>
                        <?php
                    foreach (array(1, 3, 5) as $num){
                        print "<option value='$num'>$num</option>";
                    }
                    ?>
                    </select>
                </div>
                <div class="form-group row">
                    <label class="col-sm-2 h5" for="direction">Sort Order</label>
                    <select class="form-control" name="direction" id="direction">
                        <option value="ASC">Ascending</option>
                        <option value="DESC">Descendng</option>
                    </select>
                </div>

                <!--            <div class="form-control">-->
                <!--            </div>-->
                <!--            <div class="form-control">-->
                <!--            </div>-->

            <button type="submit" class="btn btn-sm btn-success" name="mytunes_submit">Submit</button>

        </form>
    </div>


<?php


$i = 0;
while ( $query->have_posts() ) : $query->the_post();

    $metaKey = get_post_meta( $post->ID, '_mytunes_key', true );
    $metaStatus = get_post_meta( $post->ID, '_mytunes_status', true );
    $metaSource = get_post_meta( $post->ID, '_mytunes_source', true );
    $metaLinks = get_post_meta( $post->ID, '_mytunes_links', false );

    $metaKey = ($metaKey) ? $metaKey : "";
    $metaStatus = ($metaStatus) ? $metaStatus : "";
    $metaSource = ($metaSource) ? $metaSource : "";
    $metaLinks = ( is_array( $metaLinks ) && count($metaLinks) ) ? $metaLinks : array();


?>

    <div class="mytunes-post-entry">
        <div class="mytunes-post-box row">
            <div class="mytunes-title-box col-4">
                <span class="h5 "><a href="<?php echo the_permalink() ?>"><?php echo the_title(); ?></a></span>
            </div>
            <div class="mytunes-content-box col-8"><?php echo the_content(); ?></div>
        </div>

        <!-- Div to hide on load        -->
        <div class="collapse "  id="mytunes-detail-collapse-<?php echo $i; ?>">
            <div class=" mytunes-detail-box">

                <table class="table">
                    <tr>
                        <th class="text-sm-left h6">Source</th>
                        <th class="text-sm-left h6">Key</th>
                        <th class="text-sm-left h6">Difficulty</th>
                    </tr>
                    <tr>
                        <td><?php echo $metaSource ?></td>
                        <td><?php echo $metaKey ?></td>
                        <td><?php echo $metaStatus ?></td>
                    </tr>
                </table>
<!--                <div class="">-->
                    <?php
                    $linkIndex = 1;
                    if(count($metaLinks)) {
                        print "<small>Video Links</small>";
                        print "<ul>";
                        foreach($metaLinks as $link) {
                            if(is_array($link) && count($link)) {
                                print "<li><a href='". $link[0] ."'><small>".$link[0]."</small> </a></li>";
                                $linkIndex++;
                            }
                        }
                        print "</ul>";
                    }
                    ?>
<!--                </div>-->
            </div>

        </div>

        <div class="text-center">

                <a href="#mytunes-detail-collapse-<?php echo $i; ?>" data-toggle="collapse">
                    <div class="mytunes-detail-btn"></div>
                </a>
        </div>
    </div>
<?php

    $i++;
endwhile;
?>
<?php
// https://codex.wordpress.org/Function_Reference/paginate_links
$big = 999999999; // need an unlikely integer

echo paginate_links( array(
    'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
    'format' => '?paged=%#%',
    'current' => max( 1, get_query_var('paged') ),
    'total' => $query->max_num_pages
) );
?>

<?php

//wp_reset_postdata();  // Not working //
print "</div>";

get_footer();
// TODO - 1. fix spacing in boxes, fix decimal number for status, rethink status as other than a number
    // - 2. Work on single page