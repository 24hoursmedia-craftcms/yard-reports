Craft.YardReportFieldIndex = Craft.BaseElementIndex.extend(
    {

        $newFieldBtn: null,

        init: function(elementType, $container, settings) {
            this.on('selectSource', $.proxy(this, 'updateButton'));
            this.base(elementType, $container, settings);
        },

        afterInit: function() {
            this.base();
        },

        getDefaultSourceKey: function() {
            // Did they request a specific category group in the URL?
            if (this.settings.context === 'index' && typeof defaultGroupHandle !== 'undefined') {
                for (var i = 0; i < this.$sources.length; i++) {
                    var $source = $(this.$sources[i]);
                    if ($source.data('yardreportid') === defaultYardReportId) {
                        return $source.data('key');
                    }
                }
            }

            return this.base();
        },

        updateButton: function() {
            if (!this.$source) {
                return;
            }

            // Get the handle of the selected source
            var selectedYardReportId = this.$source.data('yardreportid');
            if (selectedYardReportId) {
                if (this.$newFieldBtn) {
                    this.$newFieldBtn.remove();
                }
                var newUri = Craft.getUrl('yard-reports/reports/' + selectedYardReportId + '/report-fields/new');
                var href = 'href="' + newUri + '"';
                var label = Craft.t('yard-reports', 'New Yard report field');
                this.$newFieldBtn = $('<a class="btn submit add icon" ' + href + '>' + Craft.escapeHtml(label) + '</a>');
                var indexUri = 'yard-reports/reports/' + selectedYardReportId + '/report-fields';
                history.replaceState({}, '', Craft.getUrl(indexUri));
                this.addButton(this.$newFieldBtn);
            }
        }
    });

// Register it!
Craft.registerElementIndexClass('twentyfourhoursmedia\\yardreports\\elements\\YardReportField', Craft.YardReportFieldIndex);
