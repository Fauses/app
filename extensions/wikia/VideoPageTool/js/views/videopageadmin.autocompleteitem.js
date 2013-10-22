define( 'views.videopageadmin.autocompleteitem', [
		'jquery',
		'underscore.templatecache'
	], function( $, TemplateCache ) {
		'use strict';

		var CategorySingleResultView = Backbone.View.extend({
				initialize: function( opts ) {
					this.parentView = opts.parentView;
				},
				tagName: 'div',
				className: 'autocomplete-item',
				template: TemplateCache.get( '#autocomplete-item' ),
				events: {
					'hover': 'onHover',
					'click': 'select'
				},
				onHover: function() {
					this.$el.addClass( 'selected' ).siblings().removeClass( 'selected' );
				},
				select: function( evt ) {
					this.model.collection.setCategory( this.model.get( 'name' ) );
					this.parentView.clearResults();
				},
				render: function() {
					var html = this.template( this.model.toJSON() );
					this.$el.html( html );
					return this;
				}
		});

		return CategorySingleResultView;
});
