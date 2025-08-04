<?php

/**
 * Public template
 *
 * @link       http://wordpress.org/plugins/rate-my-post/
 * @since      2.9.2
 *
 * @package    Rate_My_Post
 * @subpackage Rate_My_Post/public/partials
 */
?>

<?php
  $average_rating = Rate_My_Post_Common::get_average_rating( $post_id );
  $vote_count = Rate_My_Post_Common::get_vote_count( $post_id );
  $schema_type = $this->schema_type();
  $max_rating = Rate_My_Post_Common::max_rating();
  $image = $this->schema_image( $post_id );
  $name = wp_strip_all_tags( get_the_title( $post_id ) );
  $description = $name;
?>

<script type="application/ld+json">
{
  "@context": "http://schema.org",
  "@type": "<?php echo $schema_type; ?>",
  "aggregateRating": {
    "@type": "AggregateRating",
    "bestRating": "<?php echo $max_rating; ?>",
    "ratingCount": "<?php echo absint( $vote_count ); ?>",
    "ratingValue": "<?php echo $average_rating; ?>"
  },
  "image": "<?php echo $image; ?>",
  "name": "<?php echo $name; ?>",
  "description": "<?php echo $description; ?>"
}
</script>
