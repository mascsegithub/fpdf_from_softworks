    function pdf_generate(){
    var pMonthName = $("#month-list option:selected").text();
            var pYear = $("#year-list option:selected").text();
            var pItemName = $("#item-list option:selected").text();
            var pCountryName = $("#country-list option:selected").text();
            var pItemGroupName = $("#item-group option:selected").text();
            $.ajax({
            url: baseUrl + 'report/r_report_national_stock_summary_pdf.php',
                    type: 'post',
                    data: {
                    action: 'generateNationalSummaryReport',
                            lan: lan,
                            curTemplateName: '<?php echo $curTemplateName; ?>',
                            MonthName: pMonthName,
                            CountryName: pCountryName,
                            ItemGroupName: pItemGroupName,
                            Month: $('#month-list').val(),
                            Year: pYear,
                            Country: $('#country-list').val(),
                            ItemGroupId: $('#item-group').val()
                    },
                    success: function(response) {
                    if (response == 'Processing Error') {
                    alert('No Record Found.');
                    } else {
                    window.open('<?php echo JURI::base(); ?>' + 'reports/pdf/' + response);
                    }
                    }
            });
    }