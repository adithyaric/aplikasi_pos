import React, { useState } from "react";
import ReactDOM from "react-dom";

function Example() {
    const [count, setCount] = useState(0);

    return (
        <div className="card">
            <div className="card-body">
                <p>Data {count}</p>
                <button className="btn btn-warning" onClick={() => setCount(count + 1)}>
                    Tambahin
                </button>
                <hr />
                <button className="btn btn-danger" onClick={() => setCount(count - 1)}>
                    Kurangin
                </button>
            </div>
        </div>
    );
}

export default Example;

if (document.getElementById("example")) {
    ReactDOM.render(<Example />, document.getElementById("example"));
}
