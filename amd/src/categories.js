import $ from 'jquery';
import Ajax from 'core/ajax';

export const processResults = (selector, results) => {
    let options = [];
    $.each(results, function(index, data) {
        options.push({
            value: data.id,
            label: data.name
        });
    });
    return options;
};


export const transport = (selector, query, callback, failure) => {
    let promise;

    promise = Ajax.call([{
        methodname: 'tool_webanalytics_get_categories',
        args: {query}
    }]);

    promise[0].then(function(response) {
        callback(JSON.parse(response));
        return;

    }).fail(failure);
};